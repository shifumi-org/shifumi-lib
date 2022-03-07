<?php
global $wpdb, $armPrimaryStatus, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_social_feature, $is_multiple_membership_feature, $arm_email_settings, $arm_pay_per_post_feature;


$arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));

/**
 * Process Submited Form.
 */
if (isset($_POST['action']) && in_array($_POST['action'], array('add_member', 'update_member'))) {
    do_action('arm_admin_save_member_details', $_POST);
}
$arm_default_form_id=101;
$arm_member_form_id=0;
if (isset($_GET['arm_form_id'])) {
   $arm_member_form_id=$_GET['arm_form_id'];
   if (!is_numeric($arm_member_form_id)) {
       $arm_member_form_id=$arm_default_form_id;
   } 
}

$arm_suffix_icon_pass = '<span class="arm_visible_password_admin arm_editor_suffix" id="" style=""><i class="armfa armfa-eye"></i></span>';

$user_roles = $arm_global_settings->arm_get_all_roles();
$all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
$dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
$arm_default_FormFields=$arm_member_forms->arm_default_preset_user_fields();
if(count($arm_default_FormFields)>0){
    foreach ($arm_default_FormFields as $df_key => $df_field_value) {
        if(!isset($dbFormFields[$df_key])){
            $dbFormFields[$df_key]=$df_field_value;
        }
    }
    unset($dbFormFields['social_fields']);
}
$form_mode = __('Add New Member', 'ARMember');
$action = 'add_member';
$user_id = 0;

$arm_form_id = $arm_default_form_id;
$username = $useremail = $firstname = $last_name = $planID = '';
$u_roles = 'subscriber';
$primary_status = 1;
$secondary_status = 0;
$user = '';
$cancel_url = admin_url('admin.php?page=' . $arm_slugs->manage_members);
$required_class = 0;
$planIDs = array();
$futurePlanIDs = array();
$plan_start_date = date('m/d/Y');
$arm_member_include_fields_keys=array('user_email', 'user_pass');
if (isset($_POST['action']) && $_POST['action'] == 'add_member') {
    $username = !empty($_POST['user_login']) ? $_POST['user_login'] : '';
    $useremail = !empty($_POST['user_email']) ? $_POST['user_email'] : '';
    $firstname = !empty($_POST['first_name']) ? $_POST['first_name'] : '';
    $last_name = !empty($_POST['last_name']) ? $_POST['last_name'] : '';
    $u_roles = !empty($_POST['roles']) ? $_POST['roles'] : 'subscriber';
    if (!empty($_POST['arm_primary_status']) && $_POST['arm_primary_status'] == '1') {
        $primary_status = '1';
    } else {
        $primary_status = '2';
    }
    $planIDs = !empty($_POST['arm_user_plan']) ? $_POST['arm_user_plan'] : array();

    $planIDs = !is_array($planIDs) ? array($planIDs) : $planIDs;
}
if (isset($_GET['action']) && $_GET['action'] == 'edit_member' && !empty($_GET['id'])) {
    $form_mode = __('Update Member', 'ARMember');
    $action = 'update_member';
    $user_id = abs($_GET['id']);
    $user = $arm_members_class->arm_get_member_detail($user_id);
    $arm_form_id = isset($user->arm_form_id) ? $user->arm_form_id : 0;
    if(empty($arm_form_id)){
        $arm_form_id=$arm_default_form_id;
    }
    if($arm_form_id != 0  && $arm_form_id != ''){
        $arm_member_form_fields = $arm_member_forms->arm_get_member_forms_fields($arm_form_id, 'all');
        
        if(empty($arm_member_form_fields)){
            $arm_form_id=$arm_default_form_id;
            $arm_member_form_fields = $arm_member_forms->arm_get_member_forms_fields($arm_form_id, 'all');
        }
        if(!empty($arm_member_form_fields)){
            foreach ($arm_member_form_fields as $fields_key => $fields_value) {
                $arm_member_form_field_slug = $fields_value['arm_form_field_slug'];
                if($arm_member_form_field_slug != ''){
                    if(!in_array($fields_value['arm_form_field_option']['type'], array('section','html', 'hidden', 'submit','social_fields'))){
                        $arm_member_include_fields_keys[$arm_member_form_field_slug]=$arm_member_form_field_slug;
                        $dbFormFields[$arm_member_form_field_slug]['label'] = $fields_value['arm_form_field_option']['label'];
                        if(isset($dbFormFields[$arm_member_form_field_slug]['options']) && isset($fields_value['arm_form_field_option']['options'])){
                            $dbFormFields[$arm_member_form_field_slug]['options'] = $fields_value['arm_form_field_option']['options'];
                            
                        }
                        $dbFormFields['display_member_fields'][$arm_member_form_field_slug]=$arm_member_form_field_slug;
                    }    
                }
            }

        }
        if(isset($dbFormFields['display_member_fields']) && count($dbFormFields['display_member_fields'])){
            $dbFormFields = array_merge(array_flip($dbFormFields['display_member_fields']), $dbFormFields);
            unset($dbFormFields['display_member_fields']);
        }
        if(isset($dbFormFields['user_pass']) && isset($dbFormFields['user_pass']['required'])){
            $dbFormFields['user_pass']['required']=0;
        }
    }

    $required_class = 1;
    if (!empty($user)) {
        $arm_all_user_status = arm_get_all_member_status($user_id);
        $primary_status = $arm_all_user_status['arm_primary_status'];
        $secondary_status = $arm_all_user_status['arm_secondary_status'];
    }
    $planIDs = get_user_meta($user_id, 'arm_user_plan_ids', true);
    $planIDs = !empty($planIDs) ? $planIDs : array();
    $planID = isset($planIDs[0]) ? $planIDs[0] : 0;

    $postIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
    $postIDs = !empty($postIDs) ? $postIDs : array();
    foreach($planIDs as $plan_key => $planVal)
    {
        if(!empty($postIDs[$planVal]))
        {
            unset($planIDs[$plan_key]);
        }
    }

    $planIDs = apply_filters('arm_modify_plan_ids_externally', $planIDs, $user_id);

    $planData = get_user_meta($user_id, 'arm_user_plan_' . $planID, true);
    $plan_start_date = (isset($planData['arm_start_plan']) && !empty($planData['arm_start_plan'])) ? date('m/d/Y', $planData['arm_start_plan']) : date('m/d/Y');

    $futurePlanIDs = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
    $futurePlanIDs = !empty($futurePlanIDs) ? $futurePlanIDs : array();
    if( !empty( $futurePlanIDs ) ){
        foreach( $futurePlanIDs as $f_plan_key => $f_plan_id ){
            $paid_post_id = $arm_pay_per_post_feature->arm_get_post_from_plan_id( $f_plan_id );
            if( !empty( $paid_post_id[0]['arm_subscription_plan_id'] && !empty( $paid_post_id[0]['arm_subscription_plan_post_id'] ) ) ){
                unset( $futurePlanIDs[$f_plan_key] );
            }
        }
    }
}

$all_plan_ids = array();
if (!empty($all_active_plans)) {
    foreach ($all_active_plans as $p) {
        $all_plan_ids[] = $p['arm_subscription_plan_id'];
    }
}

$plan_to_show = array_diff($all_plan_ids, $planIDs);
$plan_to_show = array_diff($plan_to_show, $futurePlanIDs);
$plansLists = '<li data-label="' . addslashes( __('Select Plan', 'ARMember')) . '" data-value="">' . addslashes( __('Select Plan', 'ARMember') ) . '</li>';
if (!empty($all_active_plans)) {
    foreach ($all_active_plans as $p) {
        $p_id = $p['arm_subscription_plan_id'];
        if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
            if (in_array($p_id, $plan_to_show)) {
                $plansLists .= '<li data-label="' . esc_attr($p['arm_subscription_plan_name']) . '" data-value="' . $p_id . '">' . esc_attr($p['arm_subscription_plan_name']) . '</li>';
            }
        } else {
            $plansLists .= '<li data-label="' . esc_attr($p['arm_subscription_plan_name']) . '" data-value="' . $p_id . '">' . esc_attr($p['arm_subscription_plan_name']) . '</li>';
        }
    }
}



$all_subscription_plans = $arm_subscription_plans->arm_get_plans_data();
$paidPlansLists = '<li data-label="' . addslashes( __('Select Paid Post', 'ARMember')) . '" data-value="">' . addslashes( __('Select Paid Post', 'ARMember') ) . '</li>';
if (!empty($all_subscription_plans)) {
    foreach ($all_subscription_plans as $p) {
        if($p['arm_subscription_plan_post_id'] != 0 && (!in_array($p['arm_subscription_plan_id'], $planIDs)))
        {
            $p_id = $p['arm_subscription_plan_id'];
            if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                //if (in_array($p_id, $plan_to_show)) {
                $paidPlansLists .= '<li data-label="' . esc_attr($p['arm_subscription_plan_name']) . '" data-value="' . $p_id . '">' . esc_attr($p['arm_subscription_plan_name']) . '</li>';
                //}
            } else {
                $paidPlansLists .= '<li data-label="' . esc_attr($p['arm_subscription_plan_name']) . '" data-value="' . $p_id . '">' . esc_attr($p['arm_subscription_plan_name']) . '</li>';
            }
        }
    }
}



$formHiddenFields = '';

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
?>
<div class="wrap arm_page arm_add_member_page armPageContainer">
    <?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
    <div class="content_wrapper" id="content_wrapper">
        <div class="page_title"><?php echo $form_mode; ?></div>
        <div class="armclear"></div>
        <?php
        global $arm_errors;
        $errors = $arm_errors->get_error_messages();
        if (!empty($errors)) {
            foreach ($errors as $err) {
                echo '<div class="arm_message arm_error_message" style="display:block;">';
                echo '<div class="arm_message_text">' . $err . '</div>';
                echo '</div>';
            }
        }
        ?>
        <div class="armclear"></div>
        <div class="arm_add_edit_member_wrapper arm_member_detail_box">
            <form method="post" id="arm_add_edit_member_form" class="arm_add_edit_member_form arm_admin_form" enctype="multipart/form-data">
                <?php
                if (isset($_GET['action']) && $_GET['action'] == 'new' && empty($_GET['id'])) {            
                ?>
                <div class="arm_admin_form_content">
                    <?php
                    $registerForms = $arm_member_forms->arm_get_member_forms_by_type('registration', false);
                    $registerForms_List='';
		    if(is_array($registerForms) && count($registerForms)>1)
		    {
	                    if (!empty($registerForms)) {
                        
	                        foreach ($registerForms as $form) {
	                            $arm_form_id=$form['arm_form_id'];
	                            if(!empty($arm_member_form_id)){
	                                $arm_form_id=$arm_member_form_id;
	                            }
	                            $registerForms_List .= '<li data-label="' . strip_tags(stripslashes($form['arm_form_label'])) . '" data-value="' . $form['arm_form_id'] . '">' . strip_tags(stripslashes($form['arm_form_label'])) . '</li>';
	                        }
                        
	                    }
		    
		    
                    ?>
                    
                    <table class="form-table">
                        <tr class="form-field">
                            <th>
                                <label><?php _e('Select Signup / Registration Form', 'ARMember');?></label>
                            </th> 
                            <td>           
                                <div class="arm_setup_option_input arm_setup_forms_container">
                                    <div class="arm_setup_module_box">
                                        <input type="hidden" id="arm_member_form_selection" name="arm_member_form_selection" value="<?php echo $arm_form_id;?>" data-msg-required="<?php _e('Please select signup / registration form.', 'ARMember');?>" />
                                        <dl class="arm_selectbox">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_member_form_selection" class="arm_setup_form_options_list">
                                                    <?php echo $registerForms_List;?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    
                                </div>
                            </td>
                        </tr>
                    </table>
		    <?php } ?>
                </div> 
                <?php
                }
                ?>
                <div class="arm_form_main_content">
                    <div id="arm_form_guts">
                    <div id="arm_page_wrap">
                    
                    <?php
                    
                    if (isset($_GET['action']) && $_GET['action'] == 'new' && empty($_GET['id'])) {
                        
                        if($arm_form_id != 0  && $arm_form_id != ''){
                            if($arm_member_form_id!=0){
                                $arm_form_id=$arm_member_form_id;
                            }
                            $arm_member_form_fields = $arm_member_forms->arm_get_member_forms_fields($arm_form_id, 'all');
                            
                            if(!empty($arm_member_form_fields)){
                                foreach ($arm_member_form_fields as $fields_key => $fields_value) {
                                    $arm_member_form_field_slug = $fields_value['arm_form_field_slug'];
                                    if($arm_member_form_field_slug != ''){
                                        if(!in_array($fields_value['arm_form_field_option']['type'], array('section','html', 'hidden', 'submit','social_fields'))){
                                            $arm_member_include_fields_keys[$arm_member_form_field_slug]=$arm_member_form_field_slug;
                                            $dbFormFields[$arm_member_form_field_slug]['label'] = $fields_value['arm_form_field_option']['label'];
                                            $dbFormFields[$arm_member_form_field_slug]['options'] = isset($fields_value['arm_form_field_option']['options']) ? $fields_value['arm_form_field_option']['options'] : array();
                                            $dbFormFields['display_member_fields'][$arm_member_form_field_slug]=$arm_member_form_field_slug;
                                             
                                             if( !empty( isset($fields_value['arm_form_field_option']['default_val']) ) && !empty($fields_value['arm_form_field_option']['type']) && ($fields_value['arm_form_field_option']['type']=='radio' || $fields_value['arm_form_field_option']['type']=='checkbox'))
                                             {
                                                $dbFormFields[$arm_member_form_field_slug]['default_val'] = $fields_value['arm_form_field_option']['default_val'];
                                             }

                                        }    
                                    }
                                }
                                
                               
                            }
                            
                            if(isset($dbFormFields['display_member_fields']) && count($dbFormFields['display_member_fields'])){
                                $dbFormFields = array_merge(array_flip($dbFormFields['display_member_fields']), $dbFormFields);
                                unset($dbFormFields['display_member_fields']);
                            }
                        }
                    }    
                    ?>
                    <input type="hidden" name="id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="action" value="<?php echo $action ?>">
                    <input type="hidden" name="form" value="<?php echo $arm_form_id ?>">
                    <?php if (isset($_GET['action']) && $_GET['action'] == 'new' && empty($_GET['id'])) {?>
                    <input type="hidden" name="arm_member_form_has_url" id="arm_member_form_has_url" value="<?php echo admin_url('admin.php?page=arm_manage_members&action=new');?>">
                    <?php }?>
                    <div class="arm_admin_form_content">
                        <table class="form-table">
                        <?php
                        $armform = new ARM_Form();
                        if (!empty($arm_form_id) && $arm_form_id != 0) {
                            $userRegForm = $arm_member_forms->arm_get_single_member_forms($arm_form_id);
                            $arm_exists_form = $armform->arm_is_form_exists($arm_form_id);
                            if ($arm_exists_form) {
                                $armform->init((object) $userRegForm);
                            }
                        }
                        $arm_repeated_fields=array('repeat_email'=>'repeat_email');
                        if (isset($_GET['action']) && $_GET['action'] == 'new' && empty($_GET['id'])) {
                            if (!empty($dbFormFields)) {
                                foreach ($dbFormFields as $meta_key => $field) {
                                    $field_options = maybe_unserialize($field);
                                    $field_options = apply_filters('arm_change_field_options', $field_options);
                                    $meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                    $field_id = $meta_key . arm_generate_random_code();
                                    if (in_array($meta_key, $arm_member_include_fields_keys) && !in_array($meta_key,array('section', 'roles', 'html', 'hidden', 'submit','repeat_email','social_fields'))) {
                                        ?>
                                        <?php if($meta_key=='user_pass'){
                                            $amr_confirm_pass_lbl='';
                                            $arm_repeated_fields['repeat_pass']='repeat_pass';
                                            if(isset($dbFormFields['repeat_pass']) && isset($dbFormFields['repeat_pass']['label'])){
                                                $amr_confirm_pass_lbl=$dbFormFields['repeat_pass']['label'];
                                            }
                                            $amr_user_pass_lbl='';
                                            if(isset($dbFormFields['user_pass']) && isset($dbFormFields['user_pass']['label'])){
                                                $amr_user_pass_lbl=$dbFormFields['user_pass']['label'];
                                            }
                                            ?>
                                            <tr class="form-field">
                                                <th>
                                                    <label for="arm_password"><?php (!empty($amr_user_pass_lbl))? _e($amr_user_pass_lbl, 'ARMember') : _e('Password', 'ARMember'); ?><?php if ($required_class != 1): ?><span class="required_icon">*</span><?php endif; ?></label>
                                                </th>
                                                <td>
                                                <?php 
                                                    $arm_suffix_icon_pass_cls = "";
                                                    if(is_rtl()) {
                                                        $arm_suffix_icon_pass_cls = "arm_visible_password_admin_rtl";
                                                    }
                                                ?>
                                                    <input id="arm_password" class="arm_member_form_input <?php echo $arm_suffix_icon_pass_cls; ?>" name="user_pass" type="password" value="" data-msg-required="<?php _e('Password can not be left blank.', 'ARMember'); ?>" <?php if ($required_class != 1): ?>required<?php endif; ?>/>
                                                    <?php echo $arm_suffix_icon_pass; ?>
                                                </td>
                                            </tr>
                                            <tr class="form-field">
                                                <th>
                                                    <label for="arm_repeat_pass"><?php (!empty($amr_confirm_pass_lbl))? _e($amr_confirm_pass_lbl, 'ARMember') : _e('Confirm Password', 'ARMember'); ?><?php if ($required_class != 1): ?><span class="required_icon">*</span><?php endif; ?></label>
                                                </th>
                                                <td>
                                                    <input id="arm_repeat_pass" class="arm_member_form_input <?php echo $arm_suffix_icon_pass_cls; ?>" name="repeat_pass" type="password" value="" data-msg-required="<?php _e('Confirm Password can not be left blank.', 'ARMember'); ?>" <?php if ($required_class != 1): ?>required<?php endif; ?>/>
                                                    <?php echo $arm_suffix_icon_pass; ?>
                                                </td>
                                            </tr>
                                        <?php }else{?>
                                        <tr class="form-field">
                                            <th>
                                                <label for="<?php echo $field_options['id']; ?>">
                                                    <?php echo $field_options['label']; ?>
                                                    <?php echo (isset($field_options['required']) && $field_options['required'] == 1) ? '<span class="required_icon">*</span>' : ''; ?>
                                                </label>
                                            </th>
                                            <td>
                                                <div class="arm_form_fields_wrapper">
                                                    <?php
                                                    if (!empty($user)) {
                                                        $field_options['value'] = $user->$meta_key;
                                                    }
                                                    echo $arm_member_forms->arm_member_form_get_fields_by_type($field_options, $field_id, $arm_form_id, 'active', $armform);
                                                    ?>
                                                    <div class="armclear"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }?>
                                        <?php
                                    }
                                }
                            }
                            
                        }else{?>
                            <tr class="form-field form-required">
                                <th>
                                    <label for="arm_username"><?php _e('Username', 'ARMember'); ?><span class="required_icon">*</span></label>

                                </th>
                                <td>
                                    <?php
                                    $disabled = '';
                                    if (!empty($user)) {
                                        $username = $user->user_login;
                                        $disabled = 'disabled="disabled" ';
                                    }
                                    ?>
                                    <input id="arm_username" class="arm_member_form_input" type="text" name="user_login" value="<?php echo $username; ?>" <?php echo $disabled; ?> data-msg-required="<?php _e('Username can not be left blank.', 'ARMember'); ?>" required/>
                                </td>
                            </tr>
                            <?php 
                            if (!empty($dbFormFields)) {
                                foreach ($dbFormFields as $meta_key => $field) {
                                    $field_options = maybe_unserialize($field);
                                    $field_options = apply_filters('arm_change_field_options', $field_options);
                                    $meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                    $field_id = $meta_key . arm_generate_random_code();
                                    if (in_array($meta_key, $arm_member_include_fields_keys) && !in_array($meta_key,array('user_login','section', 'roles', 'html', 'hidden', 'submit','repeat_email','social_fields'))) {
                                        ?>
                                        <?php if($meta_key=='user_pass'){
                                            $arm_repeated_fields['repeat_pass']='repeat_pass';
                                            $amr_confirm_pass_lbl='';
                                            if(isset($dbFormFields['repeat_pass']) && isset($dbFormFields['repeat_pass']['label'])){
                                                $amr_confirm_pass_lbl=$dbFormFields['repeat_pass']['label'];
                                            }
                                            $amr_user_pass_lbl='';
                                            if(isset($dbFormFields['user_pass']) && isset($dbFormFields['user_pass']['label'])){
                                                $amr_user_pass_lbl=$dbFormFields['user_pass']['label'];
                                            }
                                            ?>
                                            <tr class="form-field">
                                                <th>
                                                    <label for="arm_password"><?php (!empty($amr_user_pass_lbl))? _e($amr_user_pass_lbl, 'ARMember') : _e('Password', 'ARMember'); ?><?php if ($required_class != 1): ?><span class="required_icon">*</span><?php endif; ?></label>
                                                </th>
                                                <td>
                                                <?php 
                                                    $arm_suffix_icon_pass_cls = "";
                                                    if(is_rtl()) {
                                                        $arm_suffix_icon_pass_cls = "arm_visible_password_admin_rtl";
                                                    }
                                                ?>
                                                    <input id="arm_password" class="arm_member_form_input <?php echo $arm_suffix_icon_pass_cls; ?>" name="user_pass" type="password" value="" data-msg-required="<?php _e('Password can not be left blank.', 'ARMember'); ?>" <?php if ($required_class != 1): ?>required<?php endif; ?>/>
                                                    <?php echo $arm_suffix_icon_pass; ?>
                                                </td>
                                            </tr>
                                            <tr class="form-field">
                                                <th>
                                                    <label for="arm_repeat_pass"><?php (!empty($amr_confirm_pass_lbl))? _e($amr_confirm_pass_lbl, 'ARMember') : _e('Confirm Password', 'ARMember'); ?><?php if ($required_class != 1): ?><span class="required_icon">*</span><?php endif; ?></label>
                                                </th>
                                                <td>
                                                    <input id="arm_repeat_pass" class="arm_member_form_input <?php echo $arm_suffix_icon_pass_cls; ?>" name="repeat_pass" type="password" value="" data-msg-required="<?php _e('Confirm Password can not be left blank.', 'ARMember'); ?>" <?php if ($required_class != 1): ?>required<?php endif; ?>/>
                                                    <?php echo $arm_suffix_icon_pass; ?>
                                                </td>
                                            </tr>
                                        <?php }else{?>
                                            <tr class="form-field">
                                                <th>
                                                    <label for="<?php echo $field_options['id']; ?>">
                                                        <?php echo $field_options['label']; ?>
                                                        <?php echo (isset($field_options['required']) && $field_options['required'] == 1) ? '<span class="required_icon">*</span>' : ''; ?>
                                                    </label>
                                                </th>
                                                <td>
                                                    <div class="arm_form_fields_wrapper">
                                                        <?php
                                                        if (!empty($user) && $meta_key!='user_pass') {
                                                            $field_options['value'] = $user->$meta_key;
                                                        }
                                                        echo $arm_member_forms->arm_member_form_get_fields_by_type($field_options, $field_id, $arm_form_id, 'active', $armform);
                                                        ?>
                                                        <div class="armclear"></div>
                                                    </div>
                                                </td>
                                            </tr>

                                            <?php
                                        }    
                                    }
                                }
                            }

                            ?>
                        <?php } ?>
                        <tr class="form-field"><th></th><td><a class="arm_form_additional_btn" href="javascript:void(0);"><i></i><span><?php _e('Additional Fields', 'ARMember');?></span></a></td></tr>
                    </table>
                
                </div>
            </div>
             
            <div class="arm_admin_form_content arm_member_form_additional_content">
                <table class="form-table">         
                            <?php
                           
                            $exclude_keys = array(
                                'user_login', 'user_email', 'user_pass', 'repeat_pass',
                                'arm_user_plan', 'arm_last_login_ip', 'arm_last_login_date', 'roles', 'section',
                                'repeat_pass', 'repeat_email', 'social_fields', 'avatar', 'profile_cover'
                            );
                            if (count($arm_member_include_fields_keys)>0) {
                                $exclude_keys=array_merge($exclude_keys,$arm_member_include_fields_keys);
                            }
                            if(count($arm_repeated_fields)>0){
                                foreach ($arm_repeated_fields as $field_index => $rfield_key) {
                                    unset($dbFormFields[$rfield_key]);
                                }
                                    
                            }
                            
                            if (!empty($dbFormFields)) {
                                foreach ($dbFormFields as $meta_key => $field) {
                                    $field_options = maybe_unserialize($field);
                                    $field_options = apply_filters('arm_change_field_options', $field_options);
                                    
                                    $meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                    $field_id = $meta_key . arm_generate_random_code();
                                    if (!in_array($meta_key, $exclude_keys) && !in_array($field_options['type'], array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email','social_fields'))) {
                                        ?>
                                        <tr class="form-field">
                                            <th>
                                                <label for="<?php echo $field_options['id']; ?>">
                                                    <?php echo $field_options['label']; ?>
                                                    <?php echo (isset($field_options['required']) && $field_options['required'] == 1) ? '<span class="required_icon">*</span>' : ''; ?>
                                                </label>
                                            </th>
                                            <td>
                                                <div class="arm_form_fields_wrapper">
                                                    <?php
                                                    if (!empty($user)) {
                                                        $field_options['value'] = $user->$meta_key;
                                                    }
                                                    echo $arm_member_forms->arm_member_form_get_fields_by_type($field_options, $field_id, $arm_form_id, 'active', $armform);
                                                    ?>
                                                    <div class="armclear"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            
                            ?>
                            <?php
                                /**
                                 * Add Form Hidden Fields.
                                 */
                                $form_settings = (isset($armform->settings)) ? maybe_unserialize($armform->settings) : array();
                                
                                if ($armform->exists() && isset($form_settings['is_hidden_fields']) && $form_settings['is_hidden_fields'] == '1') {
                                    if (isset($form_settings['hidden_fields']) && !empty($form_settings['hidden_fields'])) {
                                        foreach ($form_settings['hidden_fields'] as $hiddenF) {
                                            
                                            $hiddenMetaKey = (isset($hiddenF['meta_key']) && !empty($hiddenF['meta_key'])) ? $hiddenF['meta_key'] : sanitize_title('arm_hidden_' . $hiddenF['title']);
                                            $hiddenValue = get_user_meta($user_id, $hiddenMetaKey, true);
                                            $hiddenValue = (!empty($hiddenValue)) ? $hiddenValue : $hiddenF['value'];
                                            $hiddentitle = (!empty($hiddenF['title'])) ? $hiddenF['title'] : '';
                                            
                                            echo '<tr class="form-field"><th>'.$hiddentitle.'</th><td><input type="text" name="' . $hiddenMetaKey . '" value="' . $hiddenValue . '"/></td></tr>';
                                            
                                        }
                                    }
                                }
                                 
                            ?>
                            <?php
                            if(!isset($arm_member_include_fields_keys['avatar']) && !in_array('avatar', $arm_member_include_fields_keys)){
                                $avatar_field_id = 'avatar_' . arm_generate_random_code();
                                $avatarOptions = array(
                                    'id' => 'avatar',
                                    'label' => __('Avatar', 'ARMember'),
                                    'placeholder' => __('Drop file here or click to select.', 'ARMember'),
                                    'type' => 'avatar',
                                    'value' => '',
                                    'allow_ext' => '',
                                    'file_size_limit' => '2',
                                    'meta_key' => 'avatar',
                                    'required' => 0,
                                    'blank_message' => __('Please select avatar.', 'ARMember'),
                                    'invalid_message' => __('Invalid image selected.', 'ARMember'),
                                );
                                $avatarOptions = apply_filters('arm_change_field_options', $avatarOptions);
                                ?>
                                <tr class="form-field">
                                    <th>
                                        <label><?php _e('Avatar', 'ARMember'); ?></label>
                                    </th>
                                    <td>
                                        <div class="arm_form_fields_wrapper">
                                            <?php echo $arm_member_forms->arm_member_form_get_fields_by_type($avatarOptions, $avatar_field_id, $arm_form_id, 'active', $armform); ?>
                                            <div class="armclear"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                            if(!isset($arm_member_include_fields_keys['profile_cover']) && !in_array('profile_cover', $arm_member_include_fields_keys)){
                                $profile_cover_field_id = 'profile_cover_' . arm_generate_random_code();
                                $profileCoverOptions = array(
                                    'id' => 'profile_cover',
                                    'label' => __('Profile Cover', 'ARMember'),
                                    'placeholder' => __('Drop file here or click to select.', 'ARMember'),
                                    'type' => 'avatar',
                                    'value' => '',
                                    'allow_ext' => '',
                                    'file_size_limit' => '10',
                                    'meta_key' => 'profile_cover',
                                    'required' => 0,
                                    'blank_message' => __('Please select profile cover.', 'ARMember'),
                                    'invalid_message' => __('Invalid image selected.', 'ARMember'),
                                );
                                $profileCoverOptions = apply_filters('arm_change_field_options', $profileCoverOptions);
                                ?>
                                <tr class="form-field">
                                    <th>
                                        <label><?php _e('Profile Cover', 'ARMember'); ?></label>
                                    </th>
                                    <td>
                                        <div class="arm_form_fields_wrapper">
                                            <?php echo $arm_member_forms->arm_member_form_get_fields_by_type($profileCoverOptions, $profile_cover_field_id, $arm_form_id, 'active', $armform); ?>
                                            <div class="armclear"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php }?>
                </table>
            </div> 
            </div>
            </div>   
            <div class="arm_admin_form_content">
                <table class="form-table">
                    <tr class="form-field">
                        <th>
                            <label for="arm_role"><?php _e('Role (Optional)', 'ARMember'); ?></label>
                        </th>
                        <td class="arm-form-table-content">

                            <?php
                            if (!empty($user) && !empty($user->roles)) {
                                $u_roles = $user->roles;
                            } else {
                                $u_roles = array();
                            }
                            ?>

                            <select id="arm_role" class="arm_chosen_selectbox" data-msg-required="<?php _e('Select Role.', 'ARMember'); ?>" name="roles[]" data-placeholder="<?php _e('Select Role(s)..', 'ARMember'); ?>" multiple="multiple">
                                <?php if (!empty($user_roles)) { ?>
                                    <?php foreach ($user_roles as $key => $val) { ?>
                                        <option class="arm_message_selectbox_op" value="<?php echo $key; ?>" <?php
                                        if (in_array($key, $u_roles)) {
                                            echo "selected='selected'";
                                        }
                                        ?>><?php echo $val; ?></option>
                                            <?php } ?>
                                        <?php } else { ?>
                                    <option value=""><?php _e('No Roles Available', 'ARMember'); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th>
                            <label for="arm_primary_status"><?php _e('Member Status', 'ARMember'); ?></label>
                        </th>
                        <td class="arm_position_relative">
                            <div class="armswitch arm_member_status_div">
                                <input type="checkbox" id="arm_primary_status_check" <?php checked($primary_status, '1'); ?> value="1" class="armswitch_input" name="arm_primary_status"/>
                                <label for="arm_primary_status_check" class="armswitch_label arm_primary_status_check_label"></label>
                            </div>
                            <?php if ($primary_status == '1') { ?>
                                <?php
                                $arm_user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                $arm_user_plans = !empty($arm_user_plans) ? $arm_user_plans : array();
                            }
                            ?>
                            <input type="hidden" id="arm_status_switch_val" value="<?php echo $primary_status; ?>"/>
                            <div class="arm_current_status_text">
                                <?php echo $arm_members_class->armGetMemberStatusText($user_id, $primary_status); ?></div>
                            <?php
                            if ($primary_status != 1 && $primary_status != 2) {
                                $new_status = $primary_status;
                            } else {
                                $new_status = 2;
                            }
                            ?>
                            <div class="arm_inactive_status_text" style="display: none;"><?php echo $arm_members_class->armGetMemberStatusTextForAdmin($user_id, $new_status, $secondary_status); ?></div>
                            <div class="arm_active_status_text" style="display: none;"><?php echo $arm_members_class->armGetMemberStatusTextForAdmin($user_id, 1, $secondary_status); ?></div>
                        </td>
                    </tr>
                    <?php 
                     if(isset($_GET["action"]) && $_GET["action"] == "new") {
                       
                        $arm_all_email_settings = $arm_email_settings->arm_get_all_email_template();
                        $email_without_payment_status = isset($arm_all_email_settings[2]->arm_template_status) ? $arm_all_email_settings[2]->arm_template_status : '';
                        if($email_without_payment_status == "1" ) {
                        ?>
                            <tr class="form-field">
                                <th>
                                    <label for="arm_send_email"><?php _e('Send Signup Email Notification to User', 'ARMember'); ?></label>
                                </th>
                                <td>
                                    <div class="armswitch arm_send_email_to_user_div">
                                        <input type="checkbox" id="arm_send_email_check" <?php checked($email_without_payment_status, '1'); ?> value="1" class="armswitch_input" name="arm_send_email"/>
                                        <label for="arm_send_email_check" class="armswitch_label arm_send_email_check_label"></label>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                        } 
                       
                    }
                    ?>
            </table>        
            <?php
            //$planID = isset($planIDs[0]) ? $planIDs[0] : 0;
	    $planID = (isset($planIDs) && !empty($planIDs)) ? current($planIDs) : 0;

            $planObj = new ARM_Plan($planID);

            ?>    
            <table class="form-table">
                        <tr><td colspan="2"><div class="arm_solid_divider"></div><div class="page_sub_title"><?php _e('Membership Plan', 'ARMember'); ?></div></td></tr>
                        <tr>
                            <td colspan="2">
                                <div class="arm-note-message --warning">
                                    <p><?php _e('Important Note:', 'ARMember'); ?></p>
                                    <span><?php _e('All the actions like add new plan, change plan status, renew cycle, extend days, delete plan will be applied only after save button is clicked at the bottom of this page.', 'ARMember'); ?></span>
                                </div>
                            </td>
                        </tr>

                        <tr class="form-field">
                            <th>
                                <label for="arm_user_plan"><?php
                                    if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                                        _e('Add New Membership Plan', 'ARMember');
                                    } else {
                                        _e('Membership Plan', 'ARMember');
                                    }
                                    ?></label>
                            </th>
                            <td class="arm_position_relative">
                                <?php if ($is_multiple_membership_feature->isMultipleMembershipFeature) { ?>

                                    <ul class="arm_user_plan_ul" id="arm_user_plan_ul">
                                        <li class="arm_user_plan_li_0 arm_margin_bottom_20">
                                            <div class="arm_user_plns_box">
                                                <input type='hidden' class="arm_user_plan_change_input arm_mm_user_plan_change_input_get_cycle" name="arm_user_plan[]" id="arm_user_plan_0" value="" data-arm-plan-count="0"/>

                                                <dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_5">
                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd><ul data-id="arm_user_plan_0"><?php echo $plansLists; ?></ul></dd>
                                                </dl>

                                                <img src="<?php echo MEMBERSHIP_IMAGES_URL . "/add_plan.png"; ?>"  id="arm_add_new_user_plan_link" title="<?php _e('Add New Plan', 'ARMember'); ?>" onmouseover="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan.png';" class="arm_helptip_icon tipso_style arm_add_plan_icon">
                                                <img src="<?php echo MEMBERSHIP_IMAGES_URL . "/remove_plan.png"; ?>"  id="arm_remove_user_plan" title="<?php _e('Remove Plan', 'ARMember'); ?>" onmouseover="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/remove_plan_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/remove_plan.png';" class="arm_helptip_icon tipso_style arm_add_plan_icon">

                                                <div class="arm_selected_plan_cycle_0 arm_margin_top_10" style="display: none;">
                                                </div>

                                                <div class="arm_subscription_start_date_wrapper">
                                                    <span><?php _e('Plan Start Date', 'ARMember'); ?>  </span> 
                                                    <input type="text" value="<?php echo date($arm_common_date_format, strtotime(date('Y-m-d'))); ?>" data-date_format="<?php echo $arm_common_date_format; ?>"  name="arm_subscription_start_date[]" class="arm_member_form_input arm_user_plan_date_picker" />
                                                </div>
                                            </div>
                                        </li>

                                    </ul>
                                    <input type="hidden" id="arm_total_user_plans" value="1"/>

                                <?php } else {
                                    ?>
                                    <?php ?>

                                    <span class="arm_user_plan_text">
                                        <?php
                                        $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($planID);
                                        echo (!empty($plan_name)) ? $plan_name : '-';
                                        $plan_id = ($planID > 0) ? $planID : '';
                                        ?>
                                    </span>
                                    <a href="javascript:void(0)" class="arm_user_plan_change_action_btn" onclick="showUserPlanChangeBoxCallback('plan_change');"><?php _e('Change Plan', 'ARMember'); ?></a>
                                    <div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_plan_change arm_width_280" id="arm_confirm_box_plan_change" >
                                        <div class="arm_confirm_box_body">
                                            <div class="arm_confirm_box_arrow"></div>
                                            <div class="arm_confirm_box_text arm_text_align_left arm_padding_top_15">
                                                <input type='hidden' id="arm_user_plan" class="arm_user_plan_change_input arm_user_plan_change_input_get_cycle" name="arm_user_plan" data-old="<?php echo $plan_id; ?>" value="<?php echo $plan_id; ?>" data-manage-plan-grid="2"/>
                                                <span class="arm_add_plan_filter_label"><?php _e('Select New Plan', 'ARMember') ?></span>
                                                <dl class="arm_selectbox column_level_dd arm_width_230">
                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd><ul data-id="arm_user_plan"><?php echo $plansLists; ?></ul></dd>
                                                </dl>
                                                <div class="arm_selected_plan_cycle"></div>

                                                <?php if(in_array($plan_id, $planIDs)){
                                                    $display = 'none';
                                                }
                                                else{
                                                    $display = 'inline-block';
                                                }
                                                ?>

                                                <div style="display: <?php echo $display; ?>; position: relative;" class="arm_plan_start_date_box arm_margin_top_10">
                                                    <span class="arm_add_plan_filter_label"><?php _e('Plan Start Date', 'ARMember');    ?>  </span> 
                                                    <input type="text" value="<?php echo date($arm_common_date_format, strtotime($plan_start_date)); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" name="arm_subscription_start_date" class="arm_member_form_input arm_user_plan_date_picker arm_width_232 arm_min_width_232" />
                                                </div>
                                            </div>
                                            <div class='arm_confirm_box_btn_container'>
                                                <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_user_plan_change_btn arm_margin_right_5" ><?php _e('Ok', 'ARMember'); ?></button>
                                                <button type="button" class="arm_confirm_box_btn armcancel arm_user_plan_change_cancel_btn" onclick="hideUserPlanChangeBoxCallback();"><?php _e('Cancel', 'ARMember'); ?></button>
                                            </div>
                                        </div>
                                    </div> 
                                <?php } ?>
                            </td>
                        </tr>
                        <?php if (!empty($planIDs) || !empty($futurePlanIDs)) { ?>
                        <tr><td colspan="2">
                                <div class="arm_add_member_plans_div">

                                    <table class="arm_user_plan_table">
                                        <tr class="odd">
                                            <th class="arm_user_plan_text_th arm_user_plan_no"><?php _e('No', 'ARMember'); ?></th>
                                            <th class="arm_user_plan_text_th arm_user_plan_name"><?php _e('Membership Plan', 'ARMember'); ?></th>
                                            <th class="arm_user_plan_text_th arm_user_plan_type"><?php _e('Plan Type', 'ARMember'); ?></th>
                                            <th class="arm_user_plan_text_th arm_user_plan_start"><?php _e('Starts On', 'ARMember'); ?></th>
                                            <th class="arm_user_plan_text_th arm_user_plan_end"><?php _e('Expires On', 'ARMember'); ?></th>
                                            <th class="arm_user_plan_text_th arm_user_plan_cycle_date"><?php _e('Cycle Date', 'ARMember'); ?></th>
                                            <th class="arm_user_plan_text_th arm_user_plan_action"><?php _e('Action', 'ARMember'); ?></th>
                                        </tr>
                                        <?php
                                            $date_format = $arm_global_settings->arm_get_wp_date_format();
                                            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                                            $count_plans = 0;
                                            if (!empty($planIDs)) {
                                                foreach ($planIDs as $pID) {
                                                    if (!empty($pID)) {
                                                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $pID, true);
                                                        $planData = !empty($planData) ? $planData : array();

                                                        $arm_paid_condition = "";

                                                        if($arm_pay_per_post_feature->isPayPerPostFeature)
                                                        {
                                                            $arm_paid_condition = (!empty($planData) && !empty($planData['arm_current_plan_detail']) && empty($planData['arm_current_plan_detail']['arm_subscription_plan_post_id']) );
                                                        }
                                                        else
                                                        {
                                                            $arm_paid_condition = !empty($planData);    
                                                        }

                                                        if ($arm_paid_condition) {
                                                            $planDetail = $planData['arm_current_plan_detail'];
                                                            if (!empty($planDetail)) {
                                                                $planObj = new ARM_Plan(0);
                                                                $planObj->init((object) $planDetail);
                                                            } else {
                                                                $planObj = new ARM_Plan($pID);
                                                            }

                                                            $no = $count_plans;
                                                            $planName = $planObj->name;
                                                            $grace_message = '';
                                                            
                                                            $starts_date = !empty($planData['arm_start_plan']) ? $planData['arm_start_plan'] : '';
                                                            $started_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : '';

                                                            $starts_on = !empty($starts_date) ? date_i18n($date_format, $starts_date) : '-';

                                                            if($started_date != '' && $started_date <= $starts_date) {
                                                                $starts_on = date_i18n($date_format, $started_date);
                                                            }

                                                            $expires_on = !empty($planData['arm_expire_plan']) ? '<span id="arm_user_expiry_date_' . $pID . '" style="display: inline;"> ' . date_i18n($date_format, $planData['arm_expire_plan']) . ' <img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Change Expiry Date', 'ARMember') . '" data-plan_id="' . $pID . '" class="arm_edit_user_expiry_date"></span><span class="arm_width_155 arm_position_relative" id="arm_user_expiry_date_box_' . $pID . '" style="display: none;"><input type="text" value="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '"  data-date_format="'.$arm_common_date_format.'" name="arm_subscription_expiry_date_' . $pID . '" class="arm_member_form_input arm_user_plan_expiry_date_picker arm_width_120 arm_min_width_120" /><img src="' . MEMBERSHIP_IMAGES_URL . '/cancel_date_icon.png" width="11" height="11" title="' . __('Cancel', 'ARMember') . '" data-plan_id="' . $pID . '" data-plan-expire-date="' . date('m/d/Y', $planData['arm_expire_plan']) . '" class="arm_cancel_edit_user_expiry_date"></span>' : __('Never Expires', 'ARMember');
                                                            $renewal_on = !empty($planData['arm_next_due_payment']) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';
                                                            $trial_starts = !empty($planData['arm_trial_start']) ? $planData['arm_trial_start'] : '';
                                                            $trial_ends = !empty($planData['arm_trial_end']) ? $planData['arm_trial_end'] : '';
                                                            $arm_payment_mode = ( $planData['arm_payment_mode'] == 'auto_debit_subscription') ? '<br/>(' . __('Auto Debit','ARMember') . ')' : '';
                                                            $arm_payment_cycle = !empty($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';

                                                            if ($planObj->is_recurring()) {
                                                                $recurring_plan_options = $planObj->prepare_recurring_data($arm_payment_cycle);
                                                                $recurring_time = $recurring_plan_options['rec_time'];
                                                                $completed = $planData['arm_completed_recurring'];
                                                                if ($recurring_time == 'infinite' || empty($planData['arm_expire_plan'])) {
                                                                    $remaining_occurence = __('Never Expires', 'ARMember');
                                                                } else {
                                                                    $remaining_occurence = $recurring_time - $completed;
                                                                }

                                                                if (!empty($planData['arm_expire_plan'])) {
                                                                    if ($remaining_occurence == 0) {
                                                                        $renewal_on = __('No cycles due', 'ARMember');
                                                                    } else {
                                                                        $renewal_on .= "<br/>( " . $remaining_occurence . __(' cycles due', 'ARMember') . " )";
                                                                    }
                                                                }

                                                                $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                                                                $arm_grace_period_end = $planData['arm_grace_period_end'];

                                                                if ($arm_is_user_in_grace == "1" || $arm_is_user_in_grace == 1) {
                                                                    $arm_grace_period_end = date_i18n($date_format, $arm_grace_period_end);
                                                                    $grace_message .= "<br/>( " . __('grace period expires on', 'ARMember') ." ". $arm_grace_period_end . " )";
                                                                }
                                                            }

                                                            $arm_plan_is_suspended = '';

                                                            if (!empty($suspended_plan_ids)) {
                                                                if (in_array($pID, $suspended_plan_ids)) {
                                                                    $arm_plan_is_suspended = '<div class="arm_user_plan_status_div arm_position_relative" ><span class="armhelptip tipso_style arm_color_red" id="arm_user_suspend_plan_' . $pID . '" style=" cursor:pointer;" onclick="arm_show_failed_payment_history(' . $user_id . ',' . $pID . ',\'' . $planName . '\',\'' . $planData['arm_start_plan'] . '\')" title="' . __('Click here to Show failed payment history', 'ARMember') . '">(' . __('Suspended', 'ARMember') . ')</span><img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Activate Plan', 'ARMember') . '" data-plan_id="' . $pID . '" onclick="showConfirmBoxCallback(\'change_user_plan_' . $pID . '\');" class="arm_change_user_plan_img_' . $pID . '">
 
                                                                    <div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_change_user_plan_' . $pID . '" style="top:25px; right: -20px; ">
                                                                            <div class="arm_confirm_box_body">
                                                                                <div class="arm_confirm_box_arrow arm_float_right" ></div>
                                                                                <div class="arm_confirm_box_text arm_padding_top_15" ">' .
                                                                            __('Are you sure you want to active this plan?', 'ARMember') . '
                                                                                </div>
                                                                                <div class="arm_confirm_box_btn_container">
                                                                                    <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" id="arm_change_user_plan_status"  data-index="' . $pID . '" >' . __('Ok', 'ARMember') . '</button>
                                                                                    <button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">' . __('Cancel', 'ARMember') . '</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                            </div>';
                                                                }
                                                            }

                                                            $trial_active = '';
                                                            if (!empty($trial_starts)) {
                                                                if ($planData['arm_is_trial_plan'] == 1 || $planData['arm_is_trial_plan'] == '1') {
                                                                    if ($trial_starts < $planData['arm_start_plan']) {
                                                                        $trial_active = "<div class='arm_user_plan_status_div'><span class='arm_current_membership_trial_active'>(" . __('trial active', 'ARMember') . ")</span></div>";
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                            <tr class="arm_user_plan_table_tr <?php echo ($count_plans % 2 == 0) ? 'even' : 'odd'; ?>" id="arm_user_plan_div_<?php echo $count_plans; ?>">
                                                                <td><?php echo $count_plans + 1; ?></td>

                                                                <td><?php echo $planName . $arm_plan_is_suspended; ?></td>
                                                                <td><?php echo $planObj->new_user_plan_text(false, $arm_payment_cycle); ?></td>
                                                                <td><?php echo $starts_on . $trial_active; ?></td>
                                                                <td><?php echo $expires_on; ?></td>
                                                                <td><?php echo $renewal_on . $grace_message . $arm_payment_mode; ?></td>

                                                                <td>

                                                                    <?php
                                                                    if ($planObj->is_recurring() && $planData['arm_payment_mode'] == 'manual_subscription' && !in_array($pID, $futurePlanIDs)) {

                                                                        $recurringData = $planObj->prepare_recurring_data($arm_payment_cycle);

                                                                        $total_recurrence = $recurringData['rec_time'];
                                                                        $completed_rec = $planData['arm_completed_recurring'];
                                                                        ?>
                                                                        <div class="arm_position_relative arm_float_left">
                                                                            <?php
                                                                            if (!in_array($pID, $suspended_plan_ids) && $total_recurrence != $completed_rec) {
                                                                                ?>
                                                                                <a href="javascript:void(0)" id="arm_extend_cycle_days" class="arm_user_extend_renewal_date_action_btn" onclick="showConfirmBoxCallback('extend_renewal_date_<?php echo $pID; ?>');"><?php _e('Extend Days', 'ARMember'); ?></a>
                                                                                <div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_extend_renewal_date" id="arm_confirm_box_extend_renewal_date_<?php echo $pID; ?>">
                                                                                    <div class="arm_confirm_box_body">
                                                                                        <div class="arm_confirm_box_arrow"></div>
                                                                                        <div class="arm_confirm_box_text arm_padding_top_15">
                                                                                            <span class="arm_font_size_15 arm_margin_bottom_5"> <?php _e('Select how many days you want to extend in current cycle?', 'ARMember'); ?></span><div class="arm_margin_top_10">
                                                                                                <input type='hidden' id="arm_user_grace_plus_<?php echo $pID; ?>" name="arm_user_grace_plus_<?php echo $pID; ?>" value="0" class="arm_user_grace_plus"/>
                                                                                                <dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_width_83">
                                                                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                                                    <dd>
                                                                                                        <ul data-id="arm_user_grace_plus_<?php echo $pID; ?>">
                                                                                                            <?php
                                                                                                            for ($i = 0; $i <= 30; $i++) {
                                                                                                                ?>
                                                                                                                <li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li>
                                                                                                                <?php
                                                                                                            }
                                                                                                            ?>
                                                                                                        </ul>
                                                                                                    </dd>
                                                                                                </dl>&nbsp;&nbsp;<?php _e('Days', 'ARMember'); ?></div>
                                                                                        </div>
                                                                                        <div class='arm_confirm_box_btn_container'>
                                                                                            <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" onclick="hideConfirmBoxCallback();"><?php _e('Ok', 'ARMember'); ?></button>
                                                                                            <button type="button" class="arm_confirm_box_btn armcancel arm_user_extend_renewal_date_cancel_btn" onclick="hideUserExtendRenewalDateBoxCallback(<?php echo $pID; ?>);"><?php _e('Cancel', 'ARMember'); ?></button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                            <?php
                                                                            if ($total_recurrence != $completed_rec) {
                                                                                ?>   
                                                                                <a href="javascript:void(0)" class="arm_user_renew_next_cycle_action_btn" id="arm_skip_next_cycle" onclick="showConfirmBoxCallback('renew_next_cycle_<?php echo $pID; ?>');"><?php _e('Renew Cycle', 'ARMember'); ?></a>
                                                                                <div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_renew_next_cycle arm_width_280" id="arm_confirm_box_renew_next_cycle_<?php echo $pID; ?>" style="top:25px; right:45px; ">
                                                                                    <div class="arm_confirm_box_body">
                                                                                        <div class="arm_confirm_box_arrow arm_float_right" ></div>
                                                                                        <div class="arm_confirm_box_text arm_padding_top_15" >
                                                                                            <input type='hidden' id="arm_skip_next_renewal_<?php echo $pID; ?>" name="arm_skip_next_renewal_<?php echo $pID; ?>" value="0" class="arm_skip_next_renewal"/>
                                                                                            <?php _e('Are you sure you want to renew next cycle?', 'ARMember'); ?>
                                                                                        </div>
                                                                                        <div class='arm_confirm_box_btn_container'>
                                                                                            <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" onclick="RenewNextCycleOkCallback(<?php echo $pID; ?>)" ><?php _e('Ok', 'ARMember'); ?></button>
                                                                                            <button type="button" class="arm_confirm_box_btn armcancel arm_user_renew_next_cycle_cancel_btn" onclick="hideUserRenewNextCycleBoxCallback(<?php echo $pID; ?>);"><?php _e('Cancel', 'ARMember'); ?></button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <?php
                                                                            }
                                                                        }
                                                                        else if(isset($planData['arm_current_plan_detail']['arm_subscription_plan_type']) && $planData['arm_current_plan_detail']['arm_subscription_plan_type']=='paid_finite')
                                                                        {
                                                                            ?>   
                                                                            <div class="arm_position_relative arm_float_left">
                                                                                <a href="javascript:void(0)" class="arm_user_renew_next_cycle_action_btn" id="arm_skip_next_cycle" onclick="showConfirmBoxCallback('renew_next_cycle_<?php echo $pID; ?>');"><?php _e('Renew', 'ARMember'); ?></a>
                                                                                <div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_renew_next_cycle arm_width_280" id="arm_confirm_box_renew_next_cycle_<?php echo $pID; ?>" style="top:25px; right:45px; ">
                                                                                    <div class="arm_confirm_box_body">
                                                                                        <div class="arm_confirm_box_arrow" style="float: right"></div>
                                                                                        <div class="arm_confirm_box_text arm_padding_top_15" >
                                                                                            <input type='hidden' id="arm_skip_next_renewal_<?php echo $pID; ?>" name="arm_skip_next_renewal_<?php echo $pID; ?>" value="0" class="arm_skip_next_renewal"/>
                                                                                            <?php _e('Are you sure you want to renew plan?', 'ARMember'); ?>
                                                                                        </div>
                                                                                        <div class='arm_confirm_box_btn_container'>
                                                                                            <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" onclick="RenewNextCycleOkCallback(<?php echo $pID; ?>)" ><?php _e('Ok', 'ARMember'); ?></button>
                                                                                            <button type="button" class="arm_confirm_box_btn armcancel arm_user_renew_next_cycle_cancel_btn" onclick="hideUserRenewNextCycleBoxCallback(<?php echo $pID; ?>);"><?php _e('Cancel', 'ARMember'); ?></button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <?php
                                                                        }

                                                                        if (in_array($pID, $suspended_plan_ids)) {
                                                                            ?>
                                                                            <input type="hidden" name="arm_user_suspended_plan[]" value="<?php echo $pID; ?>" id="arm_user_suspended_plan_<?php echo $pID; ?>"/>
                                                                            <?php
                                                                        }

                                                                        if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                                                                            ?>
                                                                            <input type="hidden" name="arm_user_plan[]" value="<?php echo $pID; ?>"/>

                                                                            <input type="hidden" name="arm_subscription_start_date[]" value="<?php echo date('m/d/Y', $planData['arm_start_plan']); ?>"/>
                                                                            <div class="arm_position_relative arm_float_left">
                                                                                <a class="arm_remove_user_plan_div armhelptip tipso_style" href="javascript:void(0)" title="<?php _e('Remove Plan', 'ARMember'); ?>" onclick="showConfirmBoxCallback('delete_user_plan_<?php echo $pID; ?>');"></a>
                                                                                <div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_delete_user_plan_<?php echo $pID; ?>" style="top:25px; right: -20px; ">
                                                                                    <div class="arm_confirm_box_body">
                                                                                        <div class="arm_confirm_box_arrow arm_float_right"></div>
                                                                                        <div class="arm_confirm_box_text arm_padding_top_15" >

                                                                                            <?php _e('Are you sure you want to remove this plan?', 'ARMember'); ?>
                                                                                        </div>
                                                                                        <div class='arm_confirm_box_btn_container'>
                                                                                            <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_remove_user_plan_div_box arm_margin_right_5"  data-index="<?php echo $count_plans; ?>" ><?php _e('Ok', 'ARMember'); ?></button>
                                                                                            <button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();"><?php _e('Cancel', 'ARMember'); ?></button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div></div>
                                                                            <?php
                                                                        }
                                                                        ?>

                                                                </td>
                                                            </tr>


                                                            <?php
                                                            $count_plans++;
                                                        }
                                                    }
                                                }
                                            }

                                            if (!empty($futurePlanIDs)) {
                                                foreach ($futurePlanIDs as $pID) {
                                                    if (!empty($pID)) {
                                                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $pID, true);
                                                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                        $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);



                                                        if (!empty($planData)) {
                                                            $planDetail = $planData['arm_current_plan_detail'];
                                                            if (!empty($planDetail)) {
                                                                $planObj = new ARM_Plan(0);
                                                                $planObj->init((object) $planDetail);
                                                            } else {
                                                                $planObj = new ARM_Plan($pID);
                                                            }
                                                        }

                                                        $no = $count_plans;
                                                        $planName = $planObj->name;
                                                        $grace_message = '';
                                                        $starts_date = !empty($planData['arm_start_plan']) ? $planData['arm_start_plan'] : '';
                                                        $started_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : '';

                                                        $starts_on = !empty($starts_date) ? date_i18n($date_format, $starts_date) : '-';

                                                        if($started_date != '' && $started_date <= $starts_date) {
                                                            $starts_on = date_i18n($date_format, $started_date);
                                                        }
                                                        $expires_on = !empty($planData['arm_expire_plan']) ? '<span id="arm_user_expiry_date_' . $pID . '" style="display: inline;">' . date_i18n($date_format, $planData['arm_expire_plan']) . ' <img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Change Expiry Date', 'ARMember') . '" data-plan_id="' . $pID . '" class="arm_edit_user_expiry_date"></span><span id="arm_user_expiry_date_box_' . $pID . '" class="arm_position_relative" style="display: none; width: 155px;"><input type="text" value="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '" data-date_format="'.$arm_common_date_format.'"  name="arm_subscription_expiry_date_' . $pID . '" class="arm_member_form_input arm_user_plan_expiry_date_picker arm_width_120 arm_min_width_120" /><img src="' . MEMBERSHIP_IMAGES_URL . '/cancel_date_icon.png" width="11" height="11" title="' . __('Cancel', 'ARMember') . '" data-plan_id="' . $pID . '" data-plan-expire-date="' . date('m/d/Y', $planData['arm_expire_plan']) . '" class="arm_cancel_edit_user_expiry_date"></span>' : __('Never Expires', 'ARMember');
                                                        $renewal_on = !empty($planData['arm_next_due_payment']) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';
                                                        $trial_starts = !empty($planData['arm_trial_start']) ? $planData['arm_trial_start'] : '';
                                                        $trial_ends = !empty($planData['arm_trial_end']) ? $planData['arm_trial_end'] : '';
                                                        $arm_payment_mode = ( $planData['arm_payment_mode'] == 'auto_debit_subscription') ? '<br/>(' . __('Auto Debit','ARMember') . ')' : '';
                                                        $arm_payment_cycle = !empty($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';
                                                        if ($planObj->is_recurring()) {
                                                            $recurring_plan_options = $planObj->prepare_recurring_data($arm_payment_cycle);
                                                            $recurring_time = $recurring_plan_options['rec_time'];
                                                            $completed = $planData['arm_completed_recurring'];
                                                            if ($recurring_time == 'infinite' || empty($planData['arm_expire_plan'])) {
                                                                $remaining_occurence = __('Never Expires', 'ARMember');
                                                            } else {
                                                                $remaining_occurence = $recurring_time - $completed;
                                                            }

                                                            if (!empty($planData['arm_expire_plan'])) {
                                                                if ($remaining_occurence == 0) {
                                                                    $renewal_on = __('No cycles due', 'ARMember');
                                                                } else {
                                                                    $renewal_on .= "<br/>( " . $remaining_occurence . __(' cycles due', 'ARMember') . " )";
                                                                }
                                                            }
                                                            $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                                                            $arm_grace_period_end = $planData['arm_grace_period_end'];

                                                            if ($arm_is_user_in_grace == "1") {
                                                                $arm_grace_period_end = date_i18n($date_format, $arm_grace_period_end);
                                                                $grace_message .= "<br/>( " . __('grace period expires on', 'ARMember') ." ". $arm_grace_period_end . " )";
                                                            }
                                                        }

                                                        $arm_plan_is_suspended = '';

                                                        $trial_active = '';
                                                        ?>
                                                        <tr class="arm_user_plan_table_tr <?php echo ($count_plans % 2 == 0) ? 'even' : 'odd'; ?>" id="arm_user_future_plan_div_<?php echo $count_plans; ?>">
                                                            <td><?php echo $no + 1; ?></td>

                                                            <td><?php echo $planName . $arm_plan_is_suspended; ?></td>
                                                            <td><?php echo $planObj->new_user_plan_text(false, $arm_payment_cycle); ?></td>
                                                            <td><?php echo $starts_on . $trial_active; ?></td>
                                                            <td><?php echo $expires_on; ?></td>
                                                            <td><?php echo $renewal_on . $grace_message . $arm_payment_mode; ?></td>

                                                            <td>
                                                            <input name="arm_user_future_plan[]" value="<?php echo $pID; ?>" type="hidden" id="arm_user_future_plan_<?php echo $pID; ?>">
                                                            <?php
                                                                if ($is_multiple_membership_feature->isMultipleMembershipFeature) { ?>    
                                                                    <div class="arm_position_relative arm_float_left">
                                                                        <a class="arm_remove_user_plan_div armhelptip tipso_style" href="javascript:void(0)" title="<?php _e('Remove Plan', 'ARMember'); ?>" onclick="showConfirmBoxCallback('delete_user_plan_<?php echo $pID; ?>');"></a>
                                                                        <div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_delete_user_plan_<?php echo $pID; ?>" style="top:25px; right: -20px; ">
                                                                            <div class="arm_confirm_box_body">
                                                                                <div class="arm_confirm_box_arrow arm_float_right" ></div>
                                                                                <div class="arm_confirm_box_text arm_padding_top_15" >

                                                                                    <?php _e('Are you sure you want to remove this plan?', 'ARMember'); ?>
                                                                                </div>
                                                                                <div class='arm_confirm_box_btn_container'>
                                                                                    <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" id="arm_remove_user_future_plan_div"  data-index="<?php echo $count_plans; ?>" ><?php _e('Ok', 'ARMember'); ?></button>
                                                                                    <button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();"><?php _e('Cancel', 'ARMember'); ?></button>
                                                                                </div>
                                                                            </div>
                                                                        </div></div>
							    <?php } ?>
                                                            </td>





                                                        </tr>

                                                        <?php
                                                        $count_plans++;
                                                    }
                                                }
                                            }
                                        
                                        ?>
                                    </table>

                                </div>

                            </td></tr>
                        <?php } ?>





                <?php
                    if($arm_pay_per_post_feature->isPayPerPostFeature==true)
                    {
                        /*
                            Section of 'Paid Post'
                            =========================
                        */
                ?>
                        <input type="hidden" id="arm_total_user_posts" value="1">
                        <tr><td colspan="2"><div class="arm_solid_divider"></div><div class="page_sub_title"><?php _e('Paid Post', 'ARMember'); ?></div></td></tr>

                        <tr>
                            <td colspan="2">
                                <div class="arm-note-message --warning">
                                    <p><?php _e('Important Note:', 'ARMember'); ?></p>
                                    <span><?php _e('All the actions like add new post, renew cycle, extend days, delete post will be applied only after save button is clicked at the bottom of this page.', 'ARMember'); ?></span>
                                </div>                                
                            </td>
                        </tr>

                        <tr class="form-field">
                            <th>
                                <label for="arm_user_plan"><?php _e('Add New Paid Post', 'ARMember'); ?></label>
                            </th>
                            <td class="arm_position_relative">
                                <?php //if ($is_multiple_membership_feature->isMultipleMembershipFeature) { ?>

                                    <ul class="arm_user_plan_ul2" id="arm_user_plan_ul2">
                                        <li class="arm_user_plan_li_1 arm_margin_bottom_20">
                                            <div class="arm_user_plns_box">
                                                <input type='hidden' class="arm_user_plan_change_input arm_mm_user_post_change_input_get_cycle" name="arm_user_plan2[]" id="arm_user_post_1" value="" data-arm-plan-count="0"/>

                                                <dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_5">
                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd><ul data-id="arm_user_post_1"><?php echo $paidPlansLists; ?></ul></dd>
                                                </dl>

                                                <img src="<?php echo MEMBERSHIP_IMAGES_URL . "/add_plan.png"; ?>"  id="arm_add_new_user_plan_link2" title="<?php _e('Add New Post', 'ARMember'); ?>" onmouseover="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/add_plan.png';" class="arm_helptip_icon tipso_style arm_add_plan_icon">
                                                <img src="<?php echo MEMBERSHIP_IMAGES_URL . "/remove_plan.png"; ?>"  id="arm_remove_user_plan2" title="<?php _e('Remove Post', 'ARMember'); ?>" onmouseover="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/remove_plan_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIP_IMAGES_URL; ?>/remove_plan.png';" class="arm_helptip_icon tipso_style arm_add_plan_icon">

                                                <div class="arm_selected_plan_cycle_0 arm_margin_top_20" style=" display: none;">
                                                </div>

                                                <div class="arm_subscription_start_date_wrapper">
                                                    <span><?php _e('Post Start Date', 'ARMember'); ?></span> 
                                                    <input type="text" value="<?php echo date($arm_common_date_format, strtotime(date('Y-m-d'))); ?>" data-date_format="<?php echo $arm_common_date_format; ?>"  name="arm_subscription_start_date2[]" class="arm_member_form_input arm_user_plan_date_picker" />
                                                </div>
                                            </div>
                                        </li>

                                    </ul>
                                    <input type="hidden" id="arm_total_user_paid_posts" value="1"/>

                                <?php  /*} else {
                                    ?>
                                    <?php ?>

                                    <span class="arm_user_plan_text">
                                        <?php
                                        $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($planID);
                                        echo (!empty($plan_name)) ? $plan_name : '-';
                                        $plan_id = ($planID > 0) ? $planID : '';
                                        ?>
                                    </span>
                                    <a href="javascript:void(0)" class="arm_user_plan_change_action_btn" onclick="showUserPlanChangeBoxCallback('plan_change');"><?php _e('Add Post', 'ARMember'); ?></a>
                                    <div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_plan_change" id="arm_confirm_box_plan_change" style="width: 280px;">
                                        <div class="arm_confirm_box_body">
                                            <div class="arm_confirm_box_arrow"></div>
                                            <div class="arm_confirm_box_text" style="text-align: left;padding-top: 15px;">
                                                <input type='hidden' id="arm_user_plan" class="arm_user_plan_change_input arm_user_plan_change_input_get_cycle" name="arm_user_plan2" data-old="<?php echo $plan_id; ?>" value="<?php echo $plan_id; ?>" data-manage-plan-grid="2"/>
                                                <span class="arm_add_plan_filter_label"><?php _e('Select New Plan', 'ARMember') ?></span>
                                                <dl class="arm_selectbox column_level_dd">
                                                    <dt style="width: 210px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd><ul data-id="arm_user_plan"><?php echo $paidPlansLists; ?></ul></dd>
                                                </dl>
                                                <div class="arm_selected_plan_cycle"></div>

                                                <?php if(in_array($plan_id, $planIDs)){
                                                    $display = 'none';
                                                }
                                                else{
                                                    $display = 'inline-block';
                                                }
                                                ?>

                                                <div style="display: <?php echo $display; ?>; margin-top: 10px; position: relative;" class="arm_plan_start_date_box">
                                                    <span class="arm_add_plan_filter_label"><?php _e('Plan Start Date', 'ARMember');    ?>  </span> 
                                                    <input type="text" value="<?php echo date($arm_common_date_format, strtotime($plan_start_date)); ?>" data-date_format="<?php echo $arm_common_date_format; ?>" name="arm_subscription_start_date2" class="arm_member_form_input arm_user_plan_date_picker" style="width: 232px; min-width: 232px;"/>
                                                </div>
                                            </div>
                                            <div class='arm_confirm_box_btn_container'>
                                                <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_user_plan_change_btn" style="margin-right: 5px;"><?php _e('Ok', 'ARMember'); ?></button>
                                                <button type="button" class="arm_confirm_box_btn armcancel arm_user_plan_change_cancel_btn" onclick="hideUserPlanChangeBoxCallback();"><?php _e('Cancel', 'ARMember'); ?></button>
                                            </div>
                                        </div>
                                    </div> 
                                <?php }*/ ?>
                            </td>
                        </tr>
                        <?php if($arm_pay_per_post_feature->isPayPerPostFeature): ?>
                        <tr><td colspan="2">
                                
                                <?php $member_paid_post_plans = $arm_pay_per_post_feature->arm_get_paid_post_plans_paging($user_id, 1, 5);?>
                                <?php echo $member_paid_post_plans;?>
                            </td></tr>
                        <?php endif; ?>

                <?php
                        /*
                            =========================
                        */
                    }
                ?>


		</table>
		
                        <?php if ($arm_social_feature->isSocialFeature): ?>
                            <?php
                            $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
                            ?>
                            <table class="form-table">
                                <tr><td colspan="2"><div class="arm_solid_divider"></div><div class="page_sub_title"><?php _e('Social Fields', 'ARMember'); ?></div></td></tr>
                                <tr class="form-field">
                                    <th>
                                        <label><?php _e('Add Social Accounts', 'ARMember');?></label>
                                    </th> 
                                    <td class="arm-form-table-content">           
                                        <select id="arm_member_social_ac_selection" class="arm_chosen_selectbox arm_width_500" name="arm_member_social_ac_selection" data-placeholder="<?php _e('Please Select..', 'ARMember'); ?>"  data-msg-required="<?php _e('Please Select Social Account.', 'ARMember'); ?>" data-msg-already="<?php _e('This social account already added.', 'ARMember'); ?>">
                                            <option value=""><?php _e('Please Select', 'ARMember'); ?></option>
                                            <?php
                                            foreach ($socialProfileFields as $spfKey => $spfLabel) {
                                                echo '<option value="' . $spfKey . '">' . strip_tags(stripslashes($spfLabel)) . '</option>';
                                            }
                                            ?>
                                        </select> <input type="button" class="armcommonbtn" id="arm_member_add_social_account_fields_btn" onclick="arm_member_add_social_account_fields();" value="<?php _e('Add', 'ARMember') ?>">   
                                        <div class="armclear"></div>
                                        <span id="arm_member_social_ac_selection-error" class="error arm_invalid"><?php _e('Please Select account', 'ARMember'); ?></span>
                                    </td>
                                </tr>
                            </table>
                            <table class="form-table" id="arm_social_field_tbl">
                            <?php
                            if (!empty($socialProfileFields)) {
                                foreach ($socialProfileFields as $spfKey => $spfLabel) {
                                    $spfMetaKey = 'arm_social_field_' . $spfKey;
                                    $spfMetaValue = get_user_meta($user_id, $spfMetaKey, true);
                                    if(!empty($spfMetaValue)){
                                        ?>
                                        <tr class="form-field">
                                            <th>
                                                <label><?php echo $spfLabel ?></label>
                                            </th>
                                            <td>
                                                <input id="arm_social_<?php echo $spfKey; ?>" class="arm_member_form_input" name="<?php echo $spfMetaKey; ?>" type="text" value="<?php echo $spfMetaValue; ?>"/>
                                            </td>
                                        </tr>
                                        <?php
                                    }    
                                }
                            }
                            ?>
                            </table>
                        <?php endif; ?>
                    
                        <?php 
                            $outside_field_content = "";
                            echo $outside_field_content = apply_filters('arm_add_fields_in_admin_before_save_button', $outside_field_content, $user_id); 
                        ?>
		    
                    <!--<div class="arm_divider"></div>-->
                    <div class="arm_submit_btn_container">
                        <button class="arm_save_btn" type="submit"><?php _e('Save', 'ARMember'); ?></button>
                        <a class="arm_cancel_btn" href="<?php echo $cancel_url; ?>"><?php _e('Close', 'ARMember') ?></a>
                        <?php echo $formHiddenFields; ?>
                        <?php wp_nonce_field( 'arm_wp_nonce' );?>
                    </div>
                    <div class="armclear"></div>
                </div>
            </form>
            <div class="armclear"></div>
        </div>
    </div>
</div>


<div class="arm_member_plan_failed_payment_popup popup_wrapper" >


    <div class="popup_header">
        <span class="popup_close_btn arm_popup_close_btn arm_member_plan_failed_payment_close_btn"></span>

        <span class="add_rule_content"><?php _e('Total Skipped Cycles Of', 'ARMember'); ?> <span class="arm_failed_payment_plan_name"></span></span>
    </div>
    <div class="popup_content_text arm_member_plan_failed_payment_popup_text arm_text_align_center" >

        <div class="arm_width_100_pct" style=" margin: 45px auto;"> <img src="<?php echo MEMBERSHIP_IMAGES_URL . "/arm_loader.gif"; ?>"></div>

    </div>
    <div class="armclear"></div>


</div>

<script>
    var PLANLIST = '<?php echo $plansLists; ?>';
    var PLANLIST2 = '<?php echo $paidPlansLists; ?>';
    var SELECTPLANLABEL = '<?php echo addslashes( __('Select Plan', 'ARMember')); ?>';
    var PLANSTARTDATELABEL = '<?php echo addslashes( __('Plan Start Date', 'ARMember')).' '; ?>';
    var CURRENTDATE = '<?php echo date($arm_common_date_format, strtotime(date('Y-m-d'))); ?>';
    var REMOVEPLAN = '<?php echo addslashes( __('Remove Plan', 'ARMember')); ?>';
    var ADDPLAN = '<?php echo addslashes( __('Add New Plan', 'ARMember')); ?>';
    var REMOVEPLANMESSAGE = '<?php echo addslashes( __('You cannot remove all plans.', 'ARMember')); ?>';
    var IMAGEURL = "<?php echo MEMBERSHIP_IMAGES_URL; ?>";
    var ACTIVESTATUSLABEL = "<?php echo addslashes( __('Active', 'ARMember')); ?>";
    var SELECTPOSTLABEL = '<?php echo addslashes( __('Select Post', 'ARMember')); ?>';
    var POSTSTARTDATELABEL = '<?php echo addslashes( __('Post Start Date', 'ARMember')).' '; ?>';
    var ARMREMOVEPOST = '<?php echo addslashes( __('Remove Post', 'ARMember')); ?>';
    var ARMADDPOST = '<?php echo addslashes( __('Add New Post', 'ARMember')); ?>';
    var REMOVEPAIDPOSTMESSAGE = '<?php echo addslashes( __('You cannot remove all posts.', 'ARMember')); ?>';
</script>

<?php
    echo $ARMember->arm_get_need_help_html_content('manage-members-add');
?>
