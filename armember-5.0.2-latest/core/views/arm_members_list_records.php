<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $is_multiple_membership_feature, $armPrimaryStatus, $arm_pay_per_post_feature;
$date_format = $arm_global_settings->arm_get_wp_date_format();
$user_roles = get_editable_roles();
$nowDate = current_time('mysql');
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();
$filter_plan_id = (!empty($_REQUEST['plan_id']) && $_REQUEST['plan_id'] != '0') ? $_REQUEST['plan_id'] : '';
$filter_form_id = (!empty($_POST['form_id']) && $_POST['form_id'] != '0') ? $_POST['form_id'] : '0';
$filter_search = (!empty($_POST['search'])) ? $_POST['search'] : '';
$filter_member_status = (!empty($_REQUEST['member_status_id'])) ? $_REQUEST['member_status_id'] : '0';
/* * *************./Begin Set Member Grid Fields/.************** */
$grid_columns = array(
                    'avatar' => __('Avatar', 'ARMember'),
                    'ID' => __('User ID', 'ARMember'),
                    'user_login' => __('Username', 'ARMember'),
                    'user_email' => __('Email Address', 'ARMember'),
                    'arm_member_type' => __('Membership Type', 'ARMember'),
                    'arm_user_plan_ids' => __('Member Plan', 'ARMember'),
	    );
            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                
                    $grid_columns['arm_user_paid_plans'] = __('Paid Post(s)', 'ARMember');
                    
            }
	    $grid_columns['arm_primary_status'] = __('Status', 'ARMember');
                    $grid_columns['roles'] = __('User Role', 'ARMember');
                    $grid_columns['first_name'] = __('First Name', 'ARMember');
                    $grid_columns['last_name'] = __('Last Name', 'ARMember');
                    $grid_columns['display_name'] = __('Display Name', 'ARMember');
                    $grid_columns['user_registered'] = __('Joined Date', 'ARMember');


if($is_multiple_membership_feature->isMultipleMembershipFeature){ 
    unset($grid_columns['arm_member_type']); 
    unset($grid_columns['roles']);
}

$default_columns = $grid_columns;
$user_meta_keys = $arm_member_forms->arm_get_db_form_fields(true);
if (!empty($user_meta_keys)) {
    $exclude_keys = array('user_pass', 'repeat_pass', 'rememberme', 'remember_me', 'section', 'html','arm_captcha');
    //$exclude_keys = array_merge($exclude_keys, array_keys($grid_columns));
    foreach ($user_meta_keys as $umkey => $val) {
        if (!in_array($umkey, $exclude_keys)) {
            if(!empty($val['label'])){
                $grid_columns[$umkey] = $val['label'];
            }else if(empty($grid_columns[$umkey])){
                $grid_columns[$umkey] = $val['label'];
            }
        }
    }
}
/* * *************./End Set Member Grid Fields/.************** */
$user_id = get_current_user_id();
$members_show_hide_column = maybe_unserialize(get_user_meta($user_id, 'arm_members_hide_show_columns_' . $filter_form_id, true));
$column_hide = "";
$totalCount = count($grid_columns) + 2;
$totalDefaultCount = count($default_columns);
if (!empty($members_show_hide_column)) {
    $i = 1;
    foreach ($members_show_hide_column as $value) {
        if ($totalCount > $i) {
            if ($value != 1) {
                $column_hide = $column_hide . $i . ',';
            }
        }
        $i++;
    }
} else {
    $column_hide = '2,8,11,';
    $i = 1;
    foreach ($grid_columns as $value) {
        if ($totalDefaultCount < $i) {
            $column_hide = $column_hide . $i . ',';
        }
        $i++;
    }
}
$plansLists = '<li data-label="' . __('Select Plan', 'ARMember') . '" data-value="">' . __('Select Plan', 'ARMember') . '</li>';
if (!empty($all_plans)) {
    foreach ($all_plans as $p) {
        $p_id = $p['arm_subscription_plan_id'];
        if ($p['arm_subscription_plan_status'] == '1') {
            $plansLists .= '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . $p_id . '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
        }
    }
}

$total_grid_column = count($grid_columns) + 2;
$grid_column_paid_with = true;
$arm_colvis = $total_grid_column;
$grid_clmn = "";
$sort_clmn = "";
$arm_exclude_colvis='0';
if($is_multiple_membership_feature->isMultipleMembershipFeature){ 
    unset($grid_columns['arm_member_type']); 
    unset($grid_columns['roles']);
    $grid_column_paid_with = false;
    $arm_colvis = ' 1, '.$total_grid_column;
    $arm_exclude_colvis='0 , 1';
    for( $i=0; $i < $total_grid_column; $i++ ) {
        //if( $i == 3 || $i == 4 || $i ==5 || $i ==7 || $i == 8 || $i == 9 ) {
        if( $i>=3 &&  $i<=12 ) {
            if($arm_pay_per_post_feature->isPayPerPostFeature && $i!=8){
                continue;
            }
        }
        $grid_clmn .= $i . ",";
    }
    //$grid_clmn .= "0,1,2,6";
    $sort_clmn = 3;
}
else{
    
    for( $i=0; $i < $total_grid_column; $i++ ) {
        if( $i>=2 &&  $i<=13 ) {
            if($arm_pay_per_post_feature->isPayPerPostFeature && $i!=7){
                continue;
            }
        }
        $grid_clmn .= $i . ",";
    }
    //$grid_clmn .= "0,1,5";
    $sort_clmn = 2;
}

?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
    jQuery(document).on('click', '.arm_show_user_more_plans_types, .arm_show_user_more_plans', function () {
        var id = jQuery(this).attr('data-id');
        var tr = jQuery(this).closest('tr');
        var class_name = jQuery(this).closest('tr').attr('class');
        var _wpnonce = jQuery('input[name="_wpnonce"]').val();
        var row = jQuery('#armember_datatable').DataTable().row(tr);
          if (row.child.isShown()) {
              row.child.hide();
              tr.removeClass('shown');
              tr.addClass('hide');
          }
          else {
              // Open this row
              row.child.show();
              tr.removeClass('hide');
              row.child(format(row.data(),_wpnonce), class_name +" "+"arm_child_user_row").show();
              tr.addClass('shown');
          }
    });
    function format(d,_wpnonce) {
        var response1 = '</div><div class="arm_child_row_div_'+d[3]+'"><img class="arm_load_user_plans" src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/arm_loader.gif" alt="<?php _e('Load More', 'ARMember'); ?>" style="  margin-left: 530px; padding: 10px;"></div>';

        setTimeout(function () { jQuery.ajax({
            type: "POST",
            url: __ARMAJAXURL,
            data: "action=get_user_all_pan_details_for_grid&user_id=" + d[3] + "&_wpnonce=" + _wpnonce,
            dataType: 'html',
            success: function (response) {

              jQuery('.arm_child_row_div_'+d[3]).html('<div class="arm_member_grid_arrow"></div>'+response);
            }
        });},200);
       return response1;
    } 

    function show_grid_loader() {
        jQuery(".arm_hide_datatable").css('visibility', 'hidden');
        jQuery('.arm_loading_grid').show();
    }
    jQuery(document).ready(function () {
        arm_load_membership_grid(false);
        jQuery('#armmanagesearch_new').bind('keyup', function (e) {
       
            e.stopPropagation();
	    var arm_check_disable = jQuery('#arm_member_grid_filter_btn').attr('disabled');
            if (e.keyCode == 13 && arm_check_disable!='disabled') {
                arm_load_membership_grid_after_filtered();
                return false;
            }
        });
    });
    function arm_load_membership_grid_after_filtered() {
        jQuery('#arm_member_grid_filter_btn').attr('disabled', 'disabled');
        jQuery('#armember_datatable').dataTable().fnDestroy();
        arm_load_membership_grid(true);
    }
    function arm_load_membership_grid(is_filtered) {
        var __ARM_Showing = '<?php echo addslashes(__('Showing','ARMember')); ?>';
        var __ARM_Showing_empty = '<?php echo addslashes(__('Showing 0 to 0 of 0 members','ARMember')); ?>';
        var __ARM_to = '<?php echo addslashes(__('to','ARMember')); ?>';
        var __ARM_of = '<?php echo addslashes(__('of','ARMember')); ?>';
        var __ARM_MEMBERS = ' <?php echo addslashes(__('members','ARMember')); ?>';
        var __ARM_Show = '<?php echo addslashes(__('Show','ARMember')); ?> ';
        var __ARM_NO_FOUND = '<?php echo addslashes(__('No any member found.','ARMember')); ?>';
        var __ARM_NO_MATCHING = '<?php echo addslashes(__('No matching records found.','ARMember')); ?>';

        var search_term = jQuery("#armmanagesearch_new").val();
        var filtered_id = jQuery("#arm_subs_filter").val();
        var payment_mode_id = jQuery("#arm_mode_filter").val();
        var status_id = jQuery("#arm_status_filter").val();
        var meta_field_key= jQuery("#arm_meta_field_filter").val();
        var arm_filter_membership_type = jQuery("#arm_filter_membership_type");
        var db_search_term = (typeof search_term !== 'undefined' && search_term !== '') ? search_term : '';
        var db_filter_id = (typeof filtered_id !== 'undefined' && filtered_id !== '') ? filtered_id : '';
        var db_payment_mode = (typeof payment_mode_id !== 'undefined' && payment_mode_id !== '') ? payment_mode_id : '';
        var db_status_id = (typeof status_id !== 'undefined' && status_id !== '') ? status_id : '';
        var db_meta_field_key = (typeof meta_field_key !== 'undefined' && meta_field_key !== '' && meta_field_key != 0) ? meta_field_key : '';

        var filtered_data = (typeof is_filtered !== 'undefined' && is_filtered !== false) ? true : false;
        var arm_multiple_membership_list_show = (typeof arm_filter_membership_type !== 'undefined') ? arm_filter_membership_type.val() : 0;
        var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
        var _wpnonce = jQuery('input[name="_wpnonce"]').val();

        

        var oTables = jQuery('#armember_datatable').dataTable({
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_MEMBERS,
                "sInfoEmpty": __ARM_Showing_empty,
               
                "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_MEMBERS,
                "sEmptyTable": __ARM_NO_FOUND,
                "sZeroRecords": __ARM_NO_MATCHING,
            },
            "language":{
                "searchPlaceholder": "Search",
                "search":"",
            },
            "buttons":[{
                "extend":"colvis",
                "columns":":not(.noVis)",
                "className":"ColVis_Button TableTools_Button ui-button ui-state-default ColVis_MasterButton",
                "text":"<span class=\"armshowhideicon\" style=\"background-image: url(<?php echo MEMBERSHIP_IMAGES_URL; ?>/show_hide_icon.png);background-repeat: no-repeat;background-position: 0 center;padding: 0 0 0 30px;\">Show / Hide columns</span>",
            }],
            "bProcessing": false,
            "bServerSide": true,
            "sAjaxSource": ajax_url,
            "sServerMethod": "POST",
            "fnServerParams": function (aoData) {
                aoData.push({'name': 'action', 'value': 'get_member_details'});
                aoData.push({'name': 'filter_plan_id', 'value': db_filter_id});
                aoData.push({'name': 'filter_mode_id', 'value': db_payment_mode});
                aoData.push({'name': 'filter_status_id', 'value': db_status_id});
                aoData.push({'name': 'filter_meta_field_key','value': db_meta_field_key});
                aoData.push({'name': 'sSearch', 'value': db_search_term});
                aoData.push({'name': 'arm_multiple_membership_list_show', 'value': arm_multiple_membership_list_show });
                aoData.push({'name': 'sColumns', 'value':null});
                aoData.push({'name': '_wpnonce', 'value': _wpnonce});
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
                "aiExclude": [0, <?php echo $arm_colvis; ?>]
            },
            "aoColumnDefs": [
                {"sType": "html", "bVisible": false, "aTargets": [<?php echo $column_hide; ?>]},
                {"sClass": "center", "aTargets": [0]},
                {"bSortable": false, "aTargets": [<?php echo rtrim($grid_clmn,",") ?>]},
                {"aTargets":[<?php echo $arm_exclude_colvis; ?>],"sClass":"noVis"}
            ],
            "fixedColumns": false,
            "bStateSave": true,
            "iCookieDuration": 60 * 60,
            "sCookiePrefix": "arm_datatable_",
            "aLengthMenu": [10, 25, 50, 100, 150, 200],
            "fnStateSave": function (oSettings, oData) {
                oData.aaSorting = [];
                oData.abVisCols = [];
                oData.aoSearchCols = [];
                this.oApi._fnCreateCookie(
                    oSettings.sCookiePrefix + oSettings.sInstance,
                    this.oApi._fnJsonString(oData),
                    oSettings.iCookieDuration,
                    oSettings.sCookiePrefix,
                    oSettings.fnCookieCallback
                );
            },
            "aaSorting": [[<?php echo $sort_clmn;?>, 'desc']],
            "fnStateLoadParams": function (oSettings, oData) {
                oData.iLength = 10;
                oData.iStart = 0;
                //oData.oSearch.sSearch = db_search_term;
            },
            "fnPreDrawCallback": function () {
                show_grid_loader();
            },
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
                    jQuery(this).parent().addClass('armGridActionTD');
                    jQuery(this).parent().attr('data-key', 'armGridActionTD');
                });
            },
            
            "fnDrawCallback": function (oSettings) {
                jQuery('.arm_loading_grid').hide();
                arm_show_data();
                jQuery("#cb-select-all-1").prop("checked", false);
                arm_selectbox_init();
                jQuery('#arm_filter_wrapper').hide();
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
                oTables.dataTable().fnAdjustColumnSizing(false);
                jQuery('#arm_member_grid_filter_btn').removeAttr('disabled');
            }
        });

        var filter_box = jQuery('#arm_filter_wrapper').html();
        jQuery('.arm_filter_grid_list_container').find('.arm_datatable_filters_options').remove();
        jQuery('div#armember_datatable_filter').parent().append(filter_box);
        jQuery('div#armember_datatable_filter').hide();
        
        
    }
// ]]>
</script>
<div class="arm_filter_wrapper" id="arm_filter_wrapper_after_filter" style="display:none;">
    <div class="arm_datatable_filters_options">
        <div class='sltstandard'>
            <input type='hidden' id='arm_manage_bulk_action1' name="action1" value="-1" />
            <dl class="arm_selectbox arm_width_250">
                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                <dd>
                    <ul data-id="arm_manage_bulk_action1">
                        <li data-label="<?php _e('Bulk Actions', 'ARMember'); ?>" data-value="-1"><?php _e('Bulk Actions', 'ARMember'); ?></li>
                        <li data-label="<?php _e('Delete', 'ARMember'); ?>" data-value="delete_member"><?php _e('Delete', 'ARMember'); ?></li>
                        <ol><?php _e('Change Status To','ARMember');?></ol>

                    <?php 
                        foreach($armPrimaryStatus as $armPrimaryStatus_key => $armPrimaryStatus_value)
                        { 
                    ?>
                            <li data-label="<?php echo $armPrimaryStatus_value.' '. __('User', 'ARMember'); ?>" data-value="arm_user_status-<?php echo $armPrimaryStatus_key ?>"><?php echo $armPrimaryStatus_value ?> <?php _e('User', 'ARMember'); ?></li>
                    <?php 
                        }

                        if(!$is_multiple_membership_feature->isMultipleMembershipFeature)
                        {
                    ?>
                            <ol><?php _e('Change Plan To', 'ARMember'); ?></ol>
                    <?php
                        }
                        else {
                    ?>
                            <ol><?php _e('Add Plan To', 'ARMember'); ?></ol>
                    <?php
                        }

                        if (!empty($all_plans))
                        { 
                    ?>
                    <?php 
                            foreach ($all_plans as $plan): 
                                if ($plan['arm_subscription_plan_status'] == 1)
                                { 
                    ?>
                                    <li data-label="<?php echo stripslashes(esc_attr($plan['arm_subscription_plan_name'])); ?>" data-value="<?php echo $plan['arm_subscription_plan_id']; ?>"><?php echo stripslashes($plan['arm_subscription_plan_name']); ?></li>
                    <?php 
                                } 
                            endforeach; 
                    ?>
                    <?php 
                            } 
                    ?>
                    </ul>
                </dd>
            </dl>
        </div>
        <input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go', 'ARMember'); ?>"/>
    </div>
</div>
<div class="arm_members_list">
    <div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
        <div class="arm_datatable_filters_options">
            <div class='sltstandard'>
                <input type='hidden' id='arm_manage_bulk_action1' name="action1" value="-1" />
                <dl class="arm_selectbox arm_width_250">
                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                    <dd>
                        <ul data-id="arm_manage_bulk_action1">
                            <li data-label="<?php _e('Bulk Actions', 'ARMember'); ?>" data-value="-1"><?php _e('Bulk Actions', 'ARMember'); ?></li>
                            <li data-label="<?php _e('Delete', 'ARMember'); ?>" data-value="delete_member"><?php _e('Delete', 'ARMember'); ?></li>
                            <ol><?php _e('Change Status To','ARMember');?></ol>

                        <?php 
                            foreach($armPrimaryStatus as $armPrimaryStatus_key => $armPrimaryStatus_value)
                            { 
                        ?>
                                <li data-label="<?php echo $armPrimaryStatus_value.' '. __('User', 'ARMember'); ?>" data-value="arm_user_status-<?php echo $armPrimaryStatus_key ?>"><?php echo $armPrimaryStatus_value ?> <?php _e('User', 'ARMember'); ?></li>
                        <?php 
                            }

                            if(!$is_multiple_membership_feature->isMultipleMembershipFeature)
                            {
                        ?>
                                <ol><?php _e('Change Plan To','ARMember');?></ol>
                        <?php 
                            }
                            else {
                        ?>
                                <ol><?php _e('Add Plan To','ARMember');?></ol>
                        <?php
                            }
                            if (!empty($all_plans)) 
                            {
                                foreach( $all_plans as $plan ) 
                                { 
                                    if ( $plan['arm_subscription_plan_status']==1 ) 
                                    {
                        ?>
                                        <li data-label="<?php echo stripslashes(esc_attr($plan['arm_subscription_plan_name']));?>" data-value="<?php echo $plan['arm_subscription_plan_id'];?>"><?php echo stripslashes($plan['arm_subscription_plan_name']);?></li>
                    <?php 
                                    } 
                                } 
                            }
                        ?>
                        </ul>
                    </dd>
                </dl>
            </div>
            <input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php _e('Go', 'ARMember'); ?>"/>
        </div>
    </div>
    <form method="GET" id="arm_member_list_form" class="data_grid_list" onsubmit="return arm_member_list_form_bulk_action();">
        <input type="hidden" name="page" value="<?php echo $arm_slugs->manage_members; ?>" />
        <input type="hidden" name="armaction" value="list" />
        <div class="arm_datatable_filters">
            <div class="arm_dt_filter_block arm_datatable_searchbox">
                <label><input type="text" placeholder="<?php _e('Search Member', 'ARMember'); ?>" id="armmanagesearch_new" value="<?php echo $filter_search; ?>" class="arm_mng_mbrs_srch_inpt" tabindex="-1"></label>
                <!--./====================Begin Filter By Plan Box====================/.-->
                <?php 
                    $arm_formfields = $user_meta_keys;
                    if (!empty($arm_formfields)) { ?>
                        <div class="arm_filter_status_box arm_datatable_filter_item">                            
                            <input type="hidden" id="arm_meta_field_filter" class="arm_meta_field_filter" value="0" />
                            <dl class="arm_selectbox arm_width_190">
                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul data-id="arm_meta_field_filter" data-placeholder="<?php _e('Select field', 'ARMember'); ?>">
                                        <li data-label="<?php _e('Select field', 'ARMember'); ?>" data-value="0"><?php _e('Select field', 'ARMember'); ?></li>
                                    <?php
                                    foreach ($arm_formfields as $field_meta_key => $field_meta_value) { 
                                        $field_options = maybe_unserialize($field_meta_value);
                                        $field_options = apply_filters('arm_change_field_options', $field_options);
                                        $exclude_field_keys = array('user_pass','repeat_pass','arm_user_plan', 'arm_last_login_ip', 'arm_last_login_date', 'roles', 'section','repeat_pass', 'repeat_email', 'social_fields', 'avatar', 'profile_cover');
                                        $field_meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                        $field_meta_label = isset($field_options['label']) ? $field_options['label'] : '';
                                        $field_type = isset($field_options['type']) ? $field_options['type'] : array();
                                        if (!in_array($field_meta_key, $exclude_field_keys) && !in_array($field_type, array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email'))) {
                                            ?>
                                            <li data-label="<?php echo $field_meta_label ?>" data-value="<?php echo $field_meta_key ?>"><?php echo $field_meta_label ?></li>
                                            <?php 
                                            }
                                        } ?>     
                                    </ul>
                                </dd>
                            </dl>
                        </div>
                    <?php }    
                ?>
                <?php if (!empty($all_plans)): ?>
                    <div class="arm_filter_plans_box arm_datatable_filter_item">                        
                        <input type="hidden" id="arm_subs_filter" class="arm_subs_filter" value="<?php echo $filter_plan_id; ?>" />
                        <dl class="arm_multiple_selectbox arm_width_190">
                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                            <dd>
                                <ul data-id="arm_subs_filter" data-placeholder="<?php _e('Select Plans', 'ARMember'); ?>">
                                    <?php foreach ($all_plans as $plan): ?>
                                        <li data-label="<?php echo stripslashes(esc_attr($plan['arm_subscription_plan_name'])); ?>" data-value="<?php echo $plan['arm_subscription_plan_id']; ?>"><input type="checkbox" class="arm_icheckbox" value="<?php echo $plan['arm_subscription_plan_id']; ?>"/><?php echo stripslashes($plan['arm_subscription_plan_name']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </dl>
                    </div>
                    <?php 
                    /*
                    <div class="arm_filter_payment_node_box arm_datatable_filter_item">
                        <span class="arm_manage_filter_label"><?php _e('Subscription Mode', 'ARMember') ?></span>
                        <input type="hidden" id="arm_mode_filter" class="arm_mode_filter" value="" />
                        <dl class="arm_multiple_selectbox">
                            <dt style="width: 130px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                            <dd>
                                <ul data-id="arm_mode_filter" data-placeholder="<?php _e('Select Mode', 'ARMember'); ?>">
                                    <li data-label="<?php _e('Automatic', 'ARMember'); ?>" data-value="auto_debit_subscription"><input type="checkbox" class="arm_icheckbox" value="auto_debit_subscription"/><?php _e('Automatic', 'ARMember'); ?></li>
                                    <li data-label="<?php _e('Semi Automatic', 'ARMember'); ?>" data-value="manual_subscription"><input type="checkbox" class="arm_icheckbox" value="manual"/><?php _e('Semi Automatic', 'ARMember'); ?></li>
                                </ul>
                            </dd>
                        </dl>
                    </div>
                    */
                    ?>
                    <div class="arm_filter_status_box arm_datatable_filter_item">                        
                        <input type="hidden" id="arm_status_filter" class="arm_status_filter" value="<?php echo $filter_member_status; ?>" />
                        <dl class="arm_selectbox arm_width_190">
                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                            <dd>
                                <ul data-id="arm_status_filter" data-placeholder="<?php _e('Select Status', 'ARMember'); ?>">
                                    <li data-label="<?php _e('Select Status', 'ARMember'); ?>" data-value="0"><?php _e('Select Status', 'ARMember'); ?></li>
                                    <?php foreach ($armPrimaryStatus as $key => $value) { ?>
                                    <li data-label="<?php echo $value.' '. __('User', 'ARMember'); ?>" data-value="<?php echo $key ?>"><?php echo $value ?> <?php _e('User', 'ARMember'); ?></li>
                                    <?php } ?>
                                    <?php
                                    if($arm_pay_per_post_feature->isPayPerPostFeature)
                                    {
                                        $suspended_plan_user_txt = __('Suspended Plan/Post User', 'ARMember');
                                    }
                                    else{
                                        $suspended_plan_user_txt = __('Suspended Plan User', 'ARMember');
                                    }
                                    ?>
                                    <li data-label="<?php echo $suspended_plan_user_txt; ?>" data-value="5"><?php echo $suspended_plan_user_txt; ?></li>
                                </ul>
                            </dd>
                        </dl>
                    </div>

                <?php 
                    if($is_multiple_membership_feature->isMultipleMembershipFeature) {
                ?>
                        <div class="arm_datatable_filter_item arm_filter_membership_type_label">                            
                            <input type="hidden" id="arm_filter_membership_type" class="arm_filter_membership_type" value="0" />
                            <dl class="arm_selectbox arm_width_190">
                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul data-id="arm_filter_membership_type" data-placeholder="<?php _e('All Members', 'ARMember'); ?>">
                                        <li data-label="<?php _e('All Members', 'ARMember'); ?>" data-value="0"><?php _e('All Members', 'ARMember'); ?></li>
                                        <li data-label="<?php _e('Single Membership', 'ARMember'); ?>" data-value="1"><?php _e('Single Membership', 'ARMember'); ?></li>
                                        <li data-label="<?php _e('Multiple Membership', 'ARMember'); ?>" data-value="2"><?php _e('Multiple Membership', 'ARMember'); ?></li>
                                    </ul>
                                </dd>
                            </dl>
                        </div>
                <?php 
                    } 
                ?>
            
            <?php endif; ?>
                <!--./====================End Filter By Plan Box====================/.-->
                <!--./====================Begin Filter By Member Form Box====================/.-->
                <input type="hidden" id="arm_form_filter" class="arm_form_filter" value="<?php echo $filter_form_id; ?>" />
                <!--./====================End Filter By Member Form Box====================/.-->
            </div>
            <div class="arm_dt_filter_block arm_dt_filter_submit">
                <input type="button" class="armemailaddbtn" id="arm_member_grid_filter_btn" onClick="arm_load_membership_grid_after_filtered();" value="<?php _e('Apply', 'ARMember'); ?>"/>
            </div>
            <div class="armclear"></div>
        </div>
        <div id="armmainformnewlist" class="arm_filter_grid_list_container">
            <div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/loader.gif" alt="Loading.."></div>
            <div class="response_messages"></div>
            <?php do_action('arm_before_listing_members'); ?>
            <div class="armclear"></div>
            <table cellpadding="0" cellspacing="0" border="0" class="display arm_hide_datatable" id="armember_datatable">
                <thead>
                    <tr>
                    <?php if ($is_multiple_membership_feature->isMultipleMembershipFeature) { ?>
                    <th></th>
                    <?php } ?>
                        <th class="cb-select-all-th arm_max_width_60"><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
                        <?php if (!empty($grid_columns)): ?>
                            <?php foreach ($grid_columns as $key => $title): ?>
                                <th data-key="<?php echo $key; ?>" class="arm_grid_th_<?php echo $key; ?>" ><?php echo stripslashes_deep($title); ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if($grid_column_paid_with): ?>
                            <th class="center"><?php _e('Paid With', 'ARMember'); ?></th>
                        <?php endif; ?>
                        <th data-key="armGridActionTD" class="armGridActionTD noVis"></th>
                    </tr>
                </thead>
            </table>
            <div class="armclear"></div>
            <input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php _e('Show / Hide columns', 'ARMember'); ?>"/>
            <input type="hidden" name="search_grid" id="search_grid" value="<?php _e('Search', 'ARMember'); ?>"/>
            <input type="hidden" name="entries_grid" id="entries_grid" value="<?php _e('members', 'ARMember'); ?>"/>
            <input type="hidden" name="show_grid" id="show_grid" value="<?php _e('Show', 'ARMember'); ?>"/>
            <input type="hidden" name="showing_grid" id="showing_grid" value="<?php _e('Showing', 'ARMember'); ?>"/>
            <input type="hidden" name="to_grid" id="to_grid" value="<?php _e('to', 'ARMember'); ?>"/>
            <input type="hidden" name="of_grid" id="of_grid" value="<?php _e('of', 'ARMember'); ?>"/>
            <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php _e('No matching members found.', 'ARMember'); ?>"/>
            <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php _e('No any member found.', 'ARMember'); ?>"/>
            <input type="hidden" name="filter_grid" id="filter_grid" value="<?php _e('filtered from', 'ARMember'); ?>"/>
            <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php _e('total', 'ARMember'); ?>"/>
            <input type="hidden" name="total_members_grid_columns" id="total_members_grid_columns" value="<?php echo count($grid_columns); ?>"/>
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
            <?php do_action('arm_after_listing_members'); ?>
        </div>
        <div class="footer_grid"></div>
    </form>
</div>

<div class="arm_member_view_detail_container"></div>