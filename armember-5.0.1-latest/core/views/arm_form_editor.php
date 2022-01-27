<?php
global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_social_feature, $arm_subscription_plans;
$form_color_schemes = $arm_member_forms->arm_form_color_schemes();
$form_gradient_scheme = $arm_member_forms->arm_default_button_gradient_color();
$formColorSchemes = isset($form_color_schemes) ? $form_color_schemes : array();
$formButtonSchemes = isset($form_gradient_scheme) ? $form_gradient_scheme : array();
$email_tools = $arm_email_settings->arm_get_optin_settings();
$activeSocialNetworks = $arm_social_feature->arm_get_active_social_options();
$thank_you_page_id = $arm_global_settings->arm_get_single_global_settings('thank_you_page_id', 0);
$all_global_settings = $arm_global_settings->global_settings;
$form_id = $show_registration_link = $show_forgot_password_link = 0;
$prefix_name = $form_styles = '';
$form_detail = $socialFieldsOptions = $submitBtnOptions = array();
$default_form_style = $arm_member_forms->arm_default_form_style();
$sectionPlaceholder = __('Drop Fields Here.', 'ARMember');
$opt_ins_feature = get_option('arm_is_opt_ins_feature');
$form_settings = array(
    'message' => __('Form has been successfully submitted.', 'ARMember'),
    'redirect_type' => 'page',
    'redirect_page' => '',
    'redirect_url' => ARM_HOME_URL,
    'auto_login' => 0,
    'show_rememberme' => 0,
    'show_registration_link' => 0,
    'show_forgot_password_link' => 0,
    'registration_link_margin' => array(),
    'forgot_password_link_margin' => array(),
    'enable_social_login' => 0,
    'social_networks' => array(),
    'social_networks_order' => array(),
    'social_networks_settings' => array(),
    'style' => $default_form_style,
    "date_format" => "d/m/Y",
    'show_time' => 0,
    'is_hidden_fields' => 0,
    'custom_css' => ''
);
$social_networks = $social_networks_order = $formSocialNetworksSettings = array();
foreach ($activeSocialNetworks as $sk => $so) {
    if ($so['status'] == 1) {
        $social_networks[] = $sk;
    }
}
if (!empty($_GET['form_id']) && $_GET['form_id'] != 0) {
    $form_id = intval($_REQUEST['form_id']);
    //Remove fields for non-saved forms
    $delete_field_status = $wpdb->delete($ARMember->tbl_arm_form_field, array('arm_form_field_status' => 2));
    //Update field status for non-saved forms
    $update_field_status = $wpdb->update($ARMember->tbl_arm_form_field, array('arm_form_field_status' => '1'), array('arm_form_field_form_id' => $form_id));

    $form_detail = $arm_member_forms->arm_get_single_member_forms($form_id);
    $form_settings = (!empty($form_detail['arm_form_settings'])) ? maybe_unserialize($form_detail['arm_form_settings']) : array();
    $form_settings['style'] = (isset($form_settings['style'])) ? $form_settings['style'] : array();
    $form_settings['style'] = shortcode_atts($default_form_style, $form_settings['style']);
    $login_regex = "/template-login(.*?)/";
    $register_regex = "/template-registration(.*?)/";
    preg_match($login_regex, $form_detail['arm_form_slug'], $match_login);
    preg_match($register_regex, $form_detail['arm_form_slug'], $match_register);
    $reference_template = $form_detail['arm_ref_template'];
    if (isset($match_login[0]) && !empty($match_login[0])) {
        $form_detail['arm_form_type'] = 'login';
    } else if (isset($match_register[0]) && !empty($match_register[0])) {
        $form_detail['arm_form_type'] = 'registration';
    }
}
if( isset( $_GET['action'] ) && 'new_form' == $_GET['action'] && isset( $_GET['arm_form_type'] ) && 'edit_profile' == $_GET['arm_form_type'] ){
    $form_detail['arm_form_type'] = 'edit_profile';
    if( isset( $_GET['form_meta_fields'] ) && '' != $_GET['form_meta_fields'] ) {
        $form_meta_fields = explode( ',', $_GET['form_meta_fields'] );
    }
    if( empty($form_meta_fields) ){
        foreach( $form_detail['fields'] as $fkey => $fvalue ){
            if( 'submit' != $fvalue['arm_form_field_option']['type'] ){
                unset( $form_detail['fields'][$fkey] );
            }
        }
        $form_detail['fields'] = array_values( $form_detail['fields'] );
    } else {
        foreach( $form_detail['fields'] as $fkey => $fvalue ){
            if( $fvalue['arm_form_field_option']['type'] != 'submit' && !in_array( $fvalue['arm_form_field_option']['meta_key'], $form_meta_fields) ){
                unset( $form_detail['fields'][$fkey]);
            } else {
                if( $fvalue['arm_form_field_option']['type'] != 'submit' ){
                    $form_detail['fields'][$fkey]['arm_form_field_option']['default_field'] = 0;
                }
            }
        }
        $form_detail['fields'] = array_values( $form_detail['fields'] );
    }

    $form_detail_fields = array();

    foreach( $form_detail['fields'] as $ofk => $ofv ){
        if( $ofv['arm_form_field_option']['type'] != 'submit' ){
            $form_detail_fields[] = $ofv['arm_form_field_option']['meta_key'];
        }
    }
}

$isRegister = ($form_detail['arm_form_type'] == 'registration') ? true : false;
$isEditProfile = ( 'edit_profile' == $form_detail['arm_form_type'] ) ? true : false;
$formDateFormat = !empty($form_settings['date_format']) ? $form_settings['date_format'] : 'd/m/Y';
$showTimePicker = !empty($form_settings['show_time']) ? $form_settings['show_time'] : 0;
$setID = $form_detail['arm_set_id'];
$is_rtl = (isset($form_settings['style']['rtl']) && $form_settings['style']['rtl'] == '1') ? $form_settings['style']['rtl'] : '0';
//Form Classes
$form_class = '';
$formLayout = !empty($form_settings['style']['form_layout']) ? $form_settings['style']['form_layout'] : 'writer';

$form_class .= ' arm_form_' . $form_id;
$form_class .= ' arm_form_layout_' . $formLayout;
$form_class .= ' armf_layout_' . $form_settings['style']['label_position'];
$form_class .= ' armf_button_position_' . $form_settings['style']['button_position'];
$form_class .= ($form_settings['style']['label_hide'] == '1') ? ' armf_label_placeholder' : '';
$form_class .= ' armf_alignment_' . $form_settings['style']['label_align'];
$form_class .= ($is_rtl == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
if (is_rtl()) {
    $form_class .= ' arm_rtl_site';
}
$arm_form_fields_for_cl = array();

$arm_form_fields_cl_omited_fields = array("password", "html", "file", "section", "avatar", "arm_captcha");

$arm_max_field_id = 0;
if(!empty($_REQUEST['is_clone']) && $_REQUEST['is_clone'] == 1) {
    $max_field_id = $wpdb->get_row("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$ARMember->tbl_arm_form_field."'");
    if(!empty($max_field_id))
    {
    	$arm_max_field_id = $max_field_id->AUTO_INCREMENT;
    }
}
?>
<div class="wrap arm_page arm_manage_form_main_wrapper">
    <div id="content_wrapper" class="arm_manage_form_content_wrapper">
        <form id="arm_manage_form_settings_form" class="arm_admin_member_form <?php echo $form_class; ?>">
            <input type="hidden" name="form_set_id" value="<?php echo $setID; ?>" id="form_set_id" class="form_set_id">
            <?php $arm_form_action = isset($_GET['action']) ? $_GET['action'] : 'new_form'; ?>
            <?php $arm_new_set_name = isset($_GET['set_name']) ? $_GET['set_name'] : ''; ?>
            <input type="hidden" name="arm_action" id="arm_action" value="<?php echo $arm_form_action; ?>" />
            <input type="hidden" name="arm_form_id" id="arm_form_id" value="<?php echo $_REQUEST['form_id']; ?>" />
            <input type="hidden" name="arm_ref_template" id="arm_ref_template" value="<?php echo $reference_template; ?>" />
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
            <?php
            if ($isRegister || $isEditProfile) {
                $arm_new_set_name = isset($_REQUEST['arm_set_name']) ? $_REQUEST['arm_set_name'] : '';
                ?>
                <input type="hidden" name="arm_new_set_name" id="arm_new_set_name" value="<?php echo $arm_new_set_name; ?>" />
                <?php
            } else {
                $arm_new_set_name = isset($_GET['arm_set_name']) ? $_GET['arm_set_name'] : '';
                ?>
                <input type="hidden" name="arm_new_set_name" id="arm_new_set_name" value="<?php echo $arm_new_set_name; ?>" />
                <?php
            }
            ?>
            <div class="arm_editor_heading">
                <div class="page_title">
                    <?php if ($isRegister || $isEditProfile) { ?>

                        <div class="arm_header_registration_form_title">
                            <?php echo $form_label = ($_GET['action'] !== 'new_form') ? $form_detail['arm_form_label'] : $_REQUEST['arm_set_name']; ?>
                            <input type="hidden" name="arm_forms[<?php echo $_REQUEST['form_id']; ?>][arm_form_label]"  class="arm_form_field_label_wrapper_value" value="<?php echo $form_label; ?>"/>
                        </div>
                    <?php } else { ?>
                        <?php _e("Other Forms (Login / Forgot Password / Change Password)", 'ARMember'); ?>
                    <?php } ?>
                    <div class="arm_editor_heading_action_btns">
                        <a href="javascript:void(0)" id="arm_save_member_form" class="arm_save_btn"><?php _e('Save', 'ARMember') ?></a>
                        <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->manage_forms); ?>" id="arm_close_member_form" class="arm_cancel_btn"><?php _e('Close', 'ARMember') ?></a>
                        <a href="javascript:void(0)" id="arm_reset_member_form" class="arm_cancel_btn arm_form_reset_btn"><i class="armfa armfa-rotate-left"></i></a>
                    </div>
                    <?php if ($isRegister || $isEditProfile) { ?>
                        <div class="arm_form_shortcode_container" style="<?php echo ($_GET['action'] == 'new_form') ? 'display:none;' : ''; ?>" >
                            <span><?php _e('Shortcode', 'ARMember'); ?>:</span>
                            <span class="arm_form_shortcode arm_shortcode_text arm_form_shortcode_box">
                                <?php
                                    if( $isEditProfile ){
                                ?>
                                    <span class="armCopyText arm_font_size_16">[arm_profile_detail id="<?php echo $form_detail['arm_form_id']; ?>"]</span>
                                    <span class="arm_click_to_copy_text arm_font_size_16" data-code="[arm_profile_detail id='<?php echo $form_detail['arm_form_id']; ?>']" ><?php _e('Click to Copy', 'ARMember') ?></span>
                                    <span class="arm_copied_text arm_font_size_16">
                                        <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/copied_ok.png' ?>" />
                                        <?php _e('Code Copied', 'ARMember') ?>
                                    </span>    
                                <?php
                                    } else {
                                ?>
                                <span class="armCopyText arm_font_size_16">[arm_form id="<?php echo $form_detail['arm_form_id']; ?>"]</span>
                                <span class="arm_click_to_copy_text arm_font_size_16" data-code="[arm_form id='<?php echo $form_detail['arm_form_id']; ?>']" ><?php _e('Click to Copy', 'ARMember') ?></span>
                                <span class="arm_copied_text arm_font_size_16">
                                    <img src="<?php echo MEMBERSHIP_IMAGES_URL . '/copied_ok.png' ?>" />
                                    <?php _e('Code Copied', 'ARMember') ?>
                                </span>
                                <?php
                                    }
                                ?>
                            </span>
                        </div>
                    <?php } ?>
                    <div class="armclear"></div>
                </div>
            </div>
            <div class="arm_editor_wrapper">
                <?php if ($isRegister || $isEditProfile) { ?>
                    <div class="arm_editor_left">
                        <div id="tabs-container" class="arm_user_form_fields_container tabs-container">
                            <ul class="tabs-menu arm_tab_menu">
                                <li class="current"><a href="#tab-1"><?php _e('Preset Fields', 'ARMember'); ?></a></li>
                                <li><a href="#tab-2"><?php _e('Form Fields', 'ARMember'); ?></a></li>
                            </ul>
                            <div class="tab arm_form_fields_container_tab">
                                <div id="tab-1" class="arm-tab-content">
                                    <div class="arm_form_addnew_fields_section arm_form_addnew_user_fields">
                                        <?php
                                        $user_meta_keys = $arm_member_forms->arm_get_db_form_fields(true);
                                        unset($user_meta_keys['roles']);
                                        unset($user_meta_keys['avatar']);
                                        unset($user_meta_keys['plans']);
                                        unset($user_meta_keys['subscription_plan']);
                                        unset($user_meta_keys['social_login']);
                                        unset($user_meta_keys['social_fields']);
                                        unset($user_meta_keys['rememberme']);
                                        unset($user_meta_keys['arm_captcha']);
                                        if (!empty($user_meta_keys)) {
                                            ?>
                                            <div class="arm_form_addnew_title"></div>
                                            <div class="arm_form_addnew_fields_container arm_form_addnew_user_element">
                                                <ul class="arm_field_type_list"><?php
                                                    foreach ($user_meta_keys as $meta_key => $opts) {
                                                        if (strpos($meta_key, '_select_') == false) {
                                                            $fieldMetaClass = '';
                                                            if( !$isEditProfile ){
                                                                if (in_array($meta_key, array('first_name', 'last_name', 'user_login', 'user_email'))) {
                                                                    $fieldMetaClass = 'arm_disabled';
                                                                }
                                                            } else {
                                                                if( isset( $form_meta_fields ) && is_array($form_meta_fields) && in_array( $meta_key, $form_meta_fields ) ){
                                                                    $fieldMetaClass = 'arm_disabled';
                                                                }
                                                            }
                                                            ?><li class="frmfieldtypebutton arm_form_preset_fields <?php echo $fieldMetaClass; ?>" data-field_key="<?php echo $meta_key; ?>"><div class="arm_new_field"><a href="javascript:void(0);" id="<?php echo $meta_key; ?>"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/general_icon.png" alt="<?php echo $opts['label']; ?>" /><img class="arm_disabled_img" src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/general_icon_disabled.png" alt="<?php echo $opts['label']; ?>" /><?php echo stripslashes_deep($opts['label']); ?></a></div></li><?php
                                                        }
                                                    }
                                                    ?></ul>
                                            </div>
                                        <?php } ?>
                                        <div class="armclear"></div>
                                        <?php if ($arm_social_feature->isSocialFeature) { ?>
                                            <div class="arm_form_addnew_fields_container arm_form_addnew_social_fields_container">
                                                <div class="arm_form_addnew_title"><?php _e('Social Profile Fields', 'ARMember'); ?></div>
                                                <a href="javascript:void(0)" class="arm_enable_social_profile_fields_link armemailaddbtn" data-field_key="social_fields"><?php _e('Social Profile Fields', 'ARMember'); ?></a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div id="tab-2" class="arm-tab-content">
                                    <div class="arm_form_addnew_fields_section arm_form_addnew_other_fields">
                                        <div class="arm_form_addnew_title"><?php _e('Basic Fields', 'ARMember'); ?></div>
                                        <div class="arm_form_addnew_fields_container">
                                            <ul class="arm_field_type_list">
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="text"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/textbox_icon.png" alt="<?php _e('Textbox', 'ARMember'); ?>" /><?php _e('Textbox', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="password"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/password_icon.png" alt="<?php _e('Password', 'ARMember'); ?>"><?php _e('Password', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="textarea"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/textarea_icon.png" alt="<?php _e('Textarea', 'ARMember'); ?>" /><?php _e('Textarea', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="checkbox"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/checkbox_icon.png" alt="<?php _e('Checkbox', 'ARMember'); ?>" /><?php _e('Checkbox', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="radio"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/radio_icon.png" alt="<?php _e('Radio Buttons', 'ARMember'); ?>" /><?php _e('Radio Buttons', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="select"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/dropdown_icon.png" alt="<?php _e('Dropdown', 'ARMember'); ?>" /><?php _e('Dropdown', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="date"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/date_icon.png" alt="<?php _e('Date', 'ARMember'); ?>" /><?php _e('Date', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="html"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/html_text_icon.png" alt="<?php _e('Html Text', 'ARMember'); ?>" /><?php _e('Html Text', 'ARMember'); ?></a>
                                                    </div>
                                                </li>

                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="file"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/file_upload_icon.png" alt="<?php _e('File Upload', 'ARMember'); ?>" /><?php _e('File Upload', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="section"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/divider_icon.png" alt="<?php _e('Divider', 'ARMember'); ?>" /><?php _e('Divider', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="armclear"></div>
                                        <div class="arm_form_addnew_title"><?php _e('Advanced Fields', 'ARMember'); ?></div>
                                        <div class="arm_form_addnew_fields_container">
                                            <ul class="arm_field_type_list">
                                                <li class="frmfieldtypebutton arm_form_preset_fields" data-field_key="roles">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="roles"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/roles_icon.png" alt="<?php _e('Roles', 'ARMember'); ?>" /><img class="arm_disabled_img" src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/roles_icon_disabled.png" alt="<?php _e('Roles', 'ARMember'); ?>" /><?php _e('Roles', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton arm_form_preset_fields" data-field_key="avatar">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="avatar"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/avatar_icon.png" alt="<?php _e('Avatar', 'ARMember'); ?>" /><img class="arm_disabled_img" src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/avatar_icon_disabled.png" alt="<?php _e('Avatar', 'ARMember'); ?>" /><?php _e('Avatar', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                                <li class="frmfieldtypebutton arm_form_preset_fields" data-field_key="profile_cover">
                                                    <div class="arm_new_field">
                                                        <a href="javascript:void(0);" id="profile_cover"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/cover_avatar_icon.png" alt="<?php _e('Profile Cover','ARMember'); ?>" /><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/cover_avatar_icon_disabled.png" class="arm_disabled_img" alt="<?php _e('Profile Cover','ARMember'); ?>" /><?php _e( 'Profile Cover', 'ARMember'); ?></a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--./ END `.arm_editor_left`-->
                <?php } ?>
                <div class="arm_editor_center">
                    <div class="arm_message arm_success_message" id="arm_success_message"><div class="arm_message_text"></div></div>
                    <div class="arm_message arm_error_message" id="arm_error_message"><div class="arm_message_text"></div></div>
                    <div class="armclear"></div>
                    <div class="arm_editor_form_fileds_container">
                        <?php
                        $socialLoginBtns = '';
                        $otherForms = $otherFormIDs = array();
                        $mainSortableClass = 'arm_main_sortable';
                        if ($isRegister || $isEditProfile) {
                            $otherForms[] = $form_detail;
                        } else {
                            $mainSortableClass = 'arm_no_sortable arm_set_editor_ul';
                            $otherForms = $arm_member_forms->arm_get_other_member_forms($setID);
                        }
                        $otherFormsValues = array_values($otherForms);
                        $firstForm = array_shift($otherFormsValues);
                        $form_settings = (!empty($firstForm['arm_form_settings'])) ? maybe_unserialize($firstForm['arm_form_settings']) : array();
                        $form_settings['style'] = (isset($form_settings['style'])) ? $form_settings['style'] : array();
                        $form_settings['style'] = shortcode_atts($default_form_style, $form_settings['style']);
                        $form_settings['style']['form_width'] = (!empty($form_settings['style']['form_width'])) ? $form_settings['style']['form_width'] : '600';
                        $form_settings['hide_title'] = (isset($form_settings['hide_title'])) ? $form_settings['hide_title'] : '0';
                        $form_settings['is_hidden_fields'] = (isset($form_settings['is_hidden_fields'])) ? $form_settings['is_hidden_fields'] : '0';
                        $formFieldPosition = (!empty($form_settings['style']['field_position'])) ? $form_settings['style']['field_position'] : 'left';
                        $mainSortableClass .= ' arm_field_position_' . $formFieldPosition . ' ';
                        $enable_social_login = (isset($form_settings['enable_social_login'])) ? $form_settings['enable_social_login'] : 0;
                        $social_btn_type = (!empty($form_settings['style']['social_btn_type'])) ? $form_settings['style']['social_btn_type'] : 'horizontal';
                        $social_btn_align = (!empty($form_settings['style']['social_btn_align'])) ? $form_settings['style']['social_btn_align'] : 'left';
                        $enable_social_btn_separator = (isset($form_settings['style']['enable_social_btn_separator'])) ? $form_settings['style']['enable_social_btn_separator'] : 0;
                        $social_btn_separator = (isset($form_settings['style']['social_btn_separator'])) ? $form_settings['style']['social_btn_separator'] : '';
                        $social_btn_position = (isset($form_settings['style']['social_btn_position'])) ? $form_settings['style']['social_btn_position'] : 'bottom';
                        if ($enable_social_login == '1') {
                            $social_networks = (isset($form_settings['social_networks']) && $form_settings['social_networks'] != '') ? explode(',', $form_settings['social_networks']) : array();
                            $social_networks_order = (isset($form_settings['social_networks_order']) && $form_settings['social_networks_order'] != '') ? explode(',', $form_settings['social_networks_order']) : array();
                            $form_settings['social_networks_settings'] = (isset($form_settings['social_networks_settings'])) ? stripslashes_deep($form_settings['social_networks_settings']) : '';
                            $formSocialNetworksSettings = maybe_unserialize($form_settings['social_networks_settings']);
                        } else {
                            $enable_social_btn_separator = 0;
                        }
                        if ($firstForm['arm_form_type'] == 'login' && $arm_social_feature->isSocialLoginFeature) {
                            if (!empty($social_networks)) {
                                foreach ($social_networks as $sk) {
                                    $so = isset($activeSocialNetworks[$sk]) ? $activeSocialNetworks[$sk] : array();
                                    if (isset($so['status']) && $so['status'] == 1 && is_array($so)) {
                                        $so = isset($formSocialNetworksSettings[$sk]) ? $formSocialNetworksSettings[$sk] : $so;
                                        $icon_url = '';
                                        $icons = $arm_social_feature->arm_get_social_network_icons($sk);
                                        if(is_array($icons) && !empty($icons))
                                        {
                                            if (isset($so['icon'])) {
                                                if (isset($icons[$so['icon']]) && $icons[$so['icon']] != '') {
                                                    $icon_url = $icons[$so['icon']];
                                                } else {
                                                    $icon = array_slice($icons, 0, 1);
                                                    $icon_url = array_shift($icon);
                                                }
                                            } else {
                                                $icon = array_slice($icons, 0, 1);
                                                $icon_url = array_shift($icon);
                                            }
                                        }
                                        $so['label'] = isset($so['label']) ? $so['label'] : $sk;
                                        $socialLoginBtns .= '<div class="arm_social_link_container arm_social_link_container_' . $sk . '">';
                                        if (file_exists(strstr($icon_url, "//"))) {
                                            $icon_url = strstr($icon_url, "//");
                                        } else if (file_exists($icon_url)) {
                                            $icon_url = $icon_url;
                                        } else {
                                            $icon_url = $icon_url;
                                        }
                                        $socialLoginBtns .= '<a href="#"><img src="' . ($icon_url) . '" alt="' . $so['label'] . '"></a>';
                                        $socialLoginBtns .= '</div>';
                                    }
                                }
                            }
                        }
                        $show_reg_link = (isset($form_settings['show_registration_link'])) ? $form_settings['show_registration_link'] : 0;
                        $show_fp_link = (isset($form_settings['show_forgot_password_link'])) ? $form_settings['show_forgot_password_link'] : 0;
                        $arm_show_captcha_field=(isset($form_settings['show_other_form_captcha_field'])) ? $form_settings['show_other_form_captcha_field'] : 0;
                        $registration_link_label = (isset($form_settings['registration_link_label'])) ? stripslashes($form_settings['registration_link_label']) : __('Register', 'ARMember');
                        $forgot_password_link_label = (isset($form_settings['forgot_password_link_label'])) ? stripslashes($form_settings['forgot_password_link_label']) : __('Forgot Password', 'ARMember');
                        $registration_link_label = $arm_member_forms->arm_parse_login_links($registration_link_label, '#');
                        $forgot_password_link_label = $arm_member_forms->arm_parse_login_links($forgot_password_link_label, '#');
                        reset($otherForms);
                        ?>
                        <?php if (!empty($otherForms)) { ?>
                            <div class="arm_form_width_belt" data-ng-module="ARMApp" data-ng-controller="ARMCtrl" data-ng-cloak="">
                                <div class="arm_form_width_text"><?php echo $form_settings['style']['form_width'] . $form_settings['style']['form_width_type']; ?></div>
                            </div>
                            <?php $arm_form_ids = array(); ?>
                            <?php foreach ($otherForms as $oform) { ?>
                                <div class="arm_editor_form_fileds_wrapper armPageContainer" data-ng-module="ARMApp" data-ng-controller="ARMCtrl" data-ng-cloak="">
                                    <?php
                                    $oformid = $oform['arm_form_id'];
                                    array_push($arm_form_ids, $oformid);
                                    $oformarmtype = $oform['arm_form_type'];
                                    $otherFormIDs[$oform['arm_form_type']] = $oform;
                                    $aboveLinks = $belowLinks = '';
                                    $form_title_position = (!empty($form_settings['style']['form_title_position'])) ? $form_settings['style']['form_title_position'] : 'left';

                                    if (isset($_GET['action']) == 'new_form') {
                                        if (isset($_GET['arm_set_name']) && $_GET['arm_set_name'] != '' && ($oformarmtype == 'registration' || $oformarmtype == 'edit_profile') ) {
                                            if( 'edit_profile' == $oformarmtype ){
                                                $oform['arm_form_title'] = $_GET['arm_set_name'];
                                            }
                                            $oform['arm_form_label'] = $_GET['arm_set_name'];
                                        } else if (!empty($oform['arm_form_label'])) {
                                            $oform['arm_form_label'] = stripslashes($oform['arm_form_label']);
                                        } else {
                                            $oform['label'] = '';
                                        }
                                    } else {
                                        $oform['arm_form_label'] = !empty($oform['arm_form_label']) ? stripslashes($oform['arm_form_label']) : '';
                                    }
                                    ?>
                                    <?php if ($oformarmtype == 'login' && $arm_social_feature->isSocialLoginFeature) { ?>
                                        <ul class="arm_form_wrapper_container arm_no_sortable arm_set_editor_ul arm_form_wrapper_container_<?php echo $oformid; ?> arm_login_links_wrapper arm_form_field_container_armsocialicons arm_socialicons_top <?php echo ($enable_social_login != '1') ? 'hidden_section' : ''; ?> <?php echo ($social_btn_position != 'top') ? 'hidden_section2' : ''; ?>" data-form_id="<?php echo $oformid; ?>">
                                            <li class='arm_width_100_pct'>
                                                <div class="arm_social_login_btns_wrapper <?php echo 'arm_' . $social_btn_type; ?> <?php echo 'arm_align_' . $social_btn_align; ?>"><?php echo $socialLoginBtns; ?></div>
                                                <div class="arm_social_btn_separator_wrapper <?php echo ($enable_social_btn_separator != '1') ? 'hidden_section' : ''; ?>">
                                                    <?php echo $social_btn_separator; ?></div>
                                            </li>
                                        </ul>
                                    <?php } ?>
                                    <div class="page_title arm_form_heading_container arm_form_editor_form_heading <?php echo 'armalign' . $form_title_position; ?>" style="<?php echo ($form_settings['hide_title'] == '1') ? 'display:none;' : ''; ?>">
                                        <?php
                                        $formTitleClass = '';
                                        if ($isRegister || $isEditProfile) {
                                            $formTitleClass = 'arm_registration_form_title';
                                        }
                                        ?>
                                        <div class="arm_form_member_main_field_label arm_member_form_label">
                                            <span class="arm_form_field_label_wrapper_text <?php echo $formTitleClass; ?>" data-type="heading"><?php echo $oform['arm_form_title']; ?></span>
                                            <?php if (!$isRegister || $isEditProfile) { ?>
                                                <input type="hidden" name="arm_forms[<?php echo $oformid; ?>][arm_form_label]" class="arm_form_field_label_wrapper_value" value="<?php echo esc_html($oform['arm_form_title']); ?>"/>
                                            <?php } ?>

                                            <input type="hidden" name="arm_forms[<?php echo $oformid; ?>][arm_form_title]" id="arm_form_label_input_hidden_<?php echo $oformid; ?>" class="arm_form_field_label_wrapper_value" value="<?php echo esc_html($oform['arm_form_title']); ?>"/>
                                        </div>
                                        <div class="armclear"></div>
                                        <input type="hidden" name="arm_forms[<?php echo $oformid; ?>][arm_form_type]" value="<?php echo $oform['arm_form_type']; ?>"/>
                                    </div>
                                    <ul class="arm_form_wrapper_container <?php echo $mainSortableClass; ?> arm_form_wrapper_container_<?php echo $oformid; ?> arm_form_editor_middle_part <?php
                                    if ($oformarmtype == 'forgot_password') {
                                        echo "arm_form_editor_forgot_password_form";
                                    }
                                    ?><?php
                                    if ($oformarmtype == 'change_password') {
                                        echo "arm_form_editor_change_password_form";
                                    }
                                    ?>" data-form_id="<?php echo $oformid; ?>">
                                            <?php

                                            if (isset($_REQUEST['form_meta_fields']) && $_REQUEST['form_meta_fields'] !== '' ) {
                                                $meta_fields = explode(',', $_REQUEST['form_meta_fields']);
                                                $metaFields = $arm_member_forms->arm_get_db_form_fields(true);
                                                $new_meta_fields = array();
                                                $n = 1;
                                                if( !$isEditProfile ){
                                                    foreach ($metaFields as $key => $value) {
                                                        if (in_array($key, $meta_fields)) {
                                                            $new_meta_fields['arm_form_field_id'] = (($form_id * 10) + $n);
                                                            $new_meta_fields['arm_form_field_form_id'] = count($oform['fields']) + 1;
                                                            $new_meta_fields['arm_form_field_order'] = '0';

                                                            $new_meta_fields['arm_form_field_option'] = maybe_serialize($metaFields[$key]);
                                                            $new_meta_fields['arm_form_field_status'] = '2';
                                                            $new_meta_fields['arm_form_field_create_date'] = date('Y-m-d H:i:s');
                                                            array_push($oform['fields'], $new_meta_fields);
                                                            unset($new_meta_fields);
                                                            $n++;
                                                        }
                                                    }
                                                } else {
                                                    foreach( $metaFields as $key => $value ) {
                                                        if( in_array($key, $meta_fields) && !in_array($key,$form_detail_fields) ){
                                                            $new_meta_fields['arm_form_field_id'] = (($form_id * 10) + $n);
                                                            $new_meta_fields['arm_form_field_form_id'] = count($oform['fields']) + 1;
                                                            $new_meta_fields['arm_form_field_order'] = '0';

                                                            $new_meta_fields['arm_form_field_option'] = maybe_serialize($metaFields[$key]);
                                                            $new_meta_fields['arm_form_field_status'] = '2';
                                                            $new_meta_fields['arm_form_field_create_date'] = date('Y-m-d H:i:s');
                                                            array_push($oform['fields'], $new_meta_fields);
                                                            unset($new_meta_fields);
                                                            $n++;
                                                        }
                                                    }
                                                }
                                            }
                                            ?>
                                            <?php if (!empty($oform['fields'])) { ?>
                                                <?php
                                                $armForm = new ARM_Form('id', $oformid);
                                                if ($oformarmtype == 'forgot_password') {
                                                    ?>
                                                <li class="arm_margin_0">
                                                    <div class="arm_forgot_password_description" style="<?php echo (empty($oform['arm_form_settings']['description'])) ? 'display:none;' : ''; ?>"><?php echo stripslashes($oform['arm_form_settings']['description']); ?></div>
                                                </li>
                                                <?php
                                            }

                                            foreach ($oform['fields'] as $ffID => $field) {
                                                $form_field_id = !empty($arm_max_field_id) ? $arm_max_field_id : $field['arm_form_field_id'];
                                                $field_options = maybe_unserialize($field['arm_form_field_option']);

                                                if ( ($isRegister || $isEditProfile) && $field_options['type'] == 'submit') {
                                                    $submitBtnOptions = $field;
                                                } elseif ( ( $isRegister || $isEditProfile ) && $field_options['type'] == 'social_fields') {
                                                    $socialFieldsOptions = $field;
                                                } else {
                                                    $liStyle = '';
                                                    $show_rememberme = (isset($armForm->settings['show_rememberme'])) ? $armForm->settings['show_rememberme'] : 0;
                                                    if ($field_options['type'] == 'rememberme' && $show_rememberme != 1) {
                                                        $liStyle = 'display:none;';
                                                    }
                                                    $sortable_class = '';
                                                    if ($field_options['type'] == 'section') {
                                                        $sortable_class .= ' arm_section_fields_wrapper';
                                                        $margin = isset($field_options['margin']) ? $field_options['margin'] : array();
                                                        $margin['top'] = (isset($margin['top']) && is_numeric($margin['top'])) ? $margin['top'] : 20;
                                                        $margin['bottom'] = (isset($margin['bottom']) && is_numeric($margin['bottom'])) ? $margin['bottom'] : 20;
                                                        $liStyle .= 'margin-top:' . $margin['top'] . 'px !important;';
                                                        $liStyle .= 'margin-bottom:' . $margin['bottom'] . 'px !important;';
                                                    }
                                                    $ref_field_id = isset($field_options['ref_field_id']) ? $field_options['ref_field_id'] : 0;
                                                    if (isset($field_options['hide_username']) && $field_options['hide_username'] == 1) {
                                                        $hide_username_class = 'hide_username_class';
                                                    } else {
                                                        $hide_username_class = '';
                                                    }
                                                    ?><li class="arm_form_field_container arm_form_field_sortable arm_form_field_container_<?php echo $field_options['type']; ?> <?php echo $sortable_class; ?> <?php echo $hide_username_class; ?>" id="arm_form_field_container_<?php echo $form_field_id; ?>" data-field_id="<?php echo $form_field_id; ?>" data-type="<?php echo $field_options['type']; ?>" data-meta_key="<?php echo isset($field_options['meta_key']) ? $field_options['meta_key'] : ''; ?>" data-ref_field="<?php echo $ref_field_id; ?>" style="<?php echo $liStyle; ?>"><?php
                                                        if( ! in_array($field_options["type"], $arm_form_fields_cl_omited_fields) && isset($field_options["meta_key"]) ) {
                                                            $arm_form_fields_for_cl[$field_options["meta_key"]] = $field_options["label"];
                                                        }
                                                    //Generate Field HTML
                                                        
                                                    $arm_member_forms->arm_member_form_get_field_html($oformid, $form_field_id, $field_options, 'inactive', $armForm, $isEditProfile);
                                                    ?></li><!--/.End `arm_form_field_container`./--><?php
                                                    if ($oformarmtype == 'login' && $field_options['type'] == 'submit') {
                                                        ?><li class="arm_form_field_container arm_form_field_sortable arm_form_field_container_forgot_link arm_forgot_password_below_link arm_forgotpassword_link arm_form_field_container_armforgotpassword <?php echo ($show_fp_link != '1') ? 'hidden_section' : ''; ?>" id="arm_form_field_container_0_0" data-field_id="0" data-type="forgot_link" data-meta_key="forgot_link"><?php echo $forgot_password_link_label; ?></li><!--/.End `arm_form_field_container`./--><?php
                                                        }
                                                    }
						    if(!empty($arm_max_field_id))
						    {
                                                    	$arm_max_field_id++;
						    }
                                                }
                                                ?>
                                            <?php } else { ?>
                                            <li></li>
                                        <?php } ?>
                                    </ul>
                                    <?php if (!empty($submitBtnOptions)) { ?>
                                        <ul class="arm_form_wrapper_container arm_no_sortable arm_form_wrapper_container_<?php echo $oformid; ?> arm_form_editor_submit_part"  data-form_id="<?php echo $oformid; ?>">
                                            <?php if (!empty($socialFieldsOptions) && $arm_social_feature->isSocialFeature) { ?>
                                                <?php
                                                $socialFieldID = $socialFieldsOptions['arm_form_field_id'];
                                                $field_options = maybe_unserialize($socialFieldsOptions['arm_form_field_option']);
                                                ?><li class="arm_form_field_container arm_form_field_container_social_fields" id="arm_form_field_container_<?php echo $socialFieldID; ?>" data-type="social_fields" data-field_id="<?php echo $socialFieldID; ?>"><?php $arm_member_forms->arm_member_form_get_field_html($oformid, $socialFieldID, $field_options, 'inactive', $armForm); ?></li>
                                            <?php } ?>
                                            <?php
                                            $form_field_id = $submitBtnOptions['arm_form_field_id'];
                                            $field_options = maybe_unserialize($submitBtnOptions['arm_form_field_option']);
                                            ?><li class="arm_form_field_container arm_form_field_container_submit" id="arm_form_field_container_<?php echo $form_field_id; ?>" data-field_id="<?php echo $form_field_id; ?>" data-type="submit"><?php
                                                $arm_member_forms->arm_member_form_get_field_html($oformid, $form_field_id, $field_options, 'inactive', $armForm);
                                                ?></li>
                                        </ul>
                                    <?php } ?>
                                    <?php if ($oformarmtype == 'login') { ?>

                                        <ul class="arm_form_wrapper_container arm_no_sortable arm_set_editor_ul arm_form_wrapper_container_<?php echo $oformid; ?> arm_login_links_wrapper" data-form_id="<?php echo $oformid; ?>" id="arm_form_editor_all_login_options">
                                            <?php if ($arm_social_feature->isSocialLoginFeature) { ?>
                                                <li class="arm_form_field_container_armsocialicons arm_width_100_pct arm_socialicons_bottom <?php echo ($enable_social_login != '1') ? 'hidden_section' : ''; ?> <?php echo ($social_btn_position != 'bottom') ? 'hidden_section2' : ''; ?>" >
                                                    <div class="arm_social_btn_separator_wrapper <?php echo ($enable_social_btn_separator != '1') ? 'hidden_section' : ''; ?> <?php echo ($social_btn_position != 'bottom') ? 'hidden_section2' : ''; ?>">
                                                        <?php echo $social_btn_separator; ?></div>
                                                    <div class="arm_social_login_btns_wrapper <?php echo 'arm_' . $social_btn_type; ?> <?php echo 'arm_align_' . $social_btn_align; ?>"><?php echo $socialLoginBtns; ?></div>
                                                </li>
                                            <?php } ?>
                                            <li class="arm_form_field_container_armbothlink arm_width_100_pct" style="<?php echo ($show_reg_link != '1' && $show_fp_link != '1') ? 'display:none;' : ''; ?>">
                                                <span class="arm_registration_link arm_form_field_container_armregister <?php echo ($show_reg_link != '1') ? 'hidden_section' : ''; ?>" id="arm_form_field_container_armregister"><?php echo $registration_link_label; ?></span>
                                            </li>
                                        </ul>
                                    <?php } ?>
                                    <div class="armclear"></div>
                                </div>
                                <div class="arm_editor_form_divider"></div>
                            <?php } ?>
                        <?php } ?>
                        <input type="hidden" name="arm_login_form_ids" id="arm_login_form_ids" value="<?php echo isset($arm_form_ids) ? implode(',', $arm_form_ids) : ''; ?>" />
                        <div class="armclear"></div>
                    </div>
                </div><!--./ END `.arm_editor_center`-->
                <a href="javascript:void(0)" class="arm_slider_arrow arm_slider_arrow_left arm_editor_right_arrow_left armhelptip hidden_section" title="<?php _e('Open Settings & Styles', 'ARMember'); ?>" data-id="arm_editor_right"></a>
                <div class="arm_editor_right">
                    <a href="javascript:void(0)" class="arm_slider_arrow arm_slider_arrow_right armhelptip" title="<?php _e('Hide Settings & Styles', 'ARMember'); ?>" data-id="arm_editor_right"></a>
                    <div class="arm_editor_right_wrapper tabs-container" id="tabs-container1">
                        <ul class="tabs-menu arm_tab_menu">
                            <li class="current"><a href="#tabsetting-1"><?php _e('Basic Options', 'ARMember'); ?></a></li>
                            <li><a href="#tabsetting-2"><?php _e('Advanced Options', 'ARMember'); ?></a></li>
                        </ul>
                        <div class="tab arm_form_settings_styles_container arm_width_100_pct" id="arm_form_settings_styles_container" >
                            <div id="tabsetting-1" class="arm-tab-content">
                                <div class="arm_right_section_heading style_setting_main_heading"><?php _e('Styling & Formatting', 'ARMember'); ?></div>
                                <div class="arm_right_section_body">
                                    <table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
                                        <input type="hidden" class="arm_switch_radio" name="arm_form_settings[display_direction]" value="vertical">

                                        <tr class="arm_form_style_options">
                                            <td class="arm_width_100"><label class="arm_form_opt_label"><?php _e('Form Width', 'ARMember'); ?></label></td>
                                            <td>
                                                <div class="arm_right">
                                                    <input type="text" id="arm_form_width1" class="arm_form_width arm_form_setting_input armMappedTextbox arm_width_130" data-id="arm_form_width" value="<?php echo!empty($form_settings['style']['form_width']) ? $form_settings['style']['form_width'] : '600'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="arm_form_style_options">
                                            <td><label class="arm_form_opt_label"><?php _e('Input Style', 'ARMember'); ?></label></td>
                                            <td>
                                                <div class="arm_right">
                                                    <input type='hidden' id="arm_manage_form_layout" class="arm_manage_form_layout armMappedTextbox" data-id="arm_manage_form_layout1" value="<?php echo $formLayout; ?>" data-old_value="<?php echo $formLayout; ?>"/>
                                                    <dl class="arm_selectbox column_level_dd arm_width_160">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_manage_form_layout">
                                                                <li data-label="<?php _e('Standard Style', 'ARMember'); ?>" data-value="iconic"><?php _e('Standard Style', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('Material Style', 'ARMember'); ?>" data-value="writer"><?php _e('Material Style', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('Rounded Style', 'ARMember'); ?>" data-value="rounded"><?php _e('Rounded Style', 'ARMember'); ?></li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="arm_form_style_color_schemes">
                                            <td colspan="2"><label class="arm_form_opt_label"><?php _e('Color Scheme', 'ARMember'); ?></label></td>
                                        </tr>
                                        <tr class="arm_form_style_color_schemes">
                                            <td colspan="2">
                                                <div class="c_schemes">
                                                    <?php foreach ($formColorSchemes as $color => $color_opt) { ?>
                                                        <label class="arm_color_scheme_block arm_color_scheme_block_<?php echo $color; ?> <?php echo ($form_settings['style']['color_scheme'] == $color) ? 'arm_color_box_active' : ''; ?>" style="<?php echo ($color == 'custom') ? 'display:none;' : '' ?>background-color:<?php echo isset($color_opt['main_color']) ? $color_opt['main_color'] : ''; ?>;">
                                                            <input type="radio" id="arm_color_block_radio_<?php echo $color; ?>1" name="arm_ignore[color_scheme]" value="<?php echo $color; ?>" class="arm_color_block_radio armMappedRadio" data-id="arm_color_block_radio_<?php echo $color; ?>" <?php checked($form_settings['style']['color_scheme'], $color) ?>/>
                                                        </label>
                                                    <?php } ?>
                                                    <div class="armclear"></div>
                                                    <a href="javascript:void(0);" class="arm_color_scheme_nav_link"><?php _e('Advanced Options', 'ARMember'); ?></a>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="armclear"></div>
                                </div>
                                <?php if($isRegister || $isEditProfile){ ?>
                                    <div class="arm_right_section_heading"><?php _e('Google reCAPTCHA (V3)', 'ARMember'); ?></div>
                                    <div class="arm_right_section_body">
                                        <table class="arm_form_settings_style_block">

                                                <?php 
                                                $arm_recaptcha_v3_status = (isset($form_settings['arm_recaptcha_v3_status'])) ? $form_settings['arm_recaptcha_v3_status'] : 0; 

                                                $arm_recaptcha_key_status=1;
                                                $arm_recaptcha_site_key = (isset($all_global_settings['arm_recaptcha_site_key']) && !empty($all_global_settings['arm_recaptcha_site_key'])) ? $all_global_settings['arm_recaptcha_site_key'] : '';
                                                $arm_recaptcha_private_key = (isset($all_global_settings['arm_recaptcha_private_key']) && !empty($all_global_settings['arm_recaptcha_private_key'])) ? $all_global_settings['arm_recaptcha_private_key'] : '';
                                                if ($arm_recaptcha_site_key == '' || $arm_recaptcha_private_key == '') {
                                                    $arm_recaptcha_key_status=0;
                                                }

                                                ?>
                                                <tr>
                                                    <td colspan="2">
                                                        <label class="arm_form_opt_label" for="arm_recaptcha_v3_status"><?php _e('Enable Google reCAPTCHA', 'ARMember'); ?></label>
                                                        <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                            <input type="checkbox" id="arm_recaptcha_v3_status" <?php checked($arm_recaptcha_v3_status, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[arm_recaptcha_v3_status]"/>
                                                            <label for="arm_recaptcha_v3_status" class="armswitch_label"></label>
                                                            <input type="hidden" name="arm_recaptcha_key_status" id="arm_recaptcha_key_status" value="<?php echo $arm_recaptcha_key_status;?>">
                                                        </div>
                                                        <div class="arm_recaptchav3_msg" style="<?php if($arm_recaptcha_v3_status==1 && $arm_recaptcha_key_status==0){?>display:block;<?php }else{ ?>display:none;<?php }?>"><?php _e('Please setup site key and private key in General Settings otherwise recaptcha will not appear', 'ARMember'); ?></div>
                                                    </td>
                                                </tr>
                                        </table>    
                                    </div>
                                <?php }?>        
                                <?php

                                    if($isRegister)
                                    {
                                ?>
                                        <div class="arm_right_section_heading"><?php _e('Register Form Options', 'ARMember'); ?></div>
                                        <div class="arm_right_section_body arm_form_redirection_options arm_padding_bottom_15">
                                            <table class="arm_form_settings_style_block">
                                            <?php $show_login_link = (isset($form_settings['show_login_link'])) ? $form_settings['show_login_link'] : 0; ?>
                                                <tr>
                                                    <td colspan="2">
                                                        <label class="arm_form_opt_label" for="show_login_link"><?php _e('Display Login Link?', 'ARMember'); ?></label>
                                                        <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                            <input type="checkbox" id="show_login_link" <?php checked($show_login_link, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[show_login_link]"/>
                                                            <label for="show_login_link" class="armswitch_label"></label>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <?php
                                                    $arm_default_login_link = __('Already have an account?', 'ARMember');
                                                    $arm_default_login_link .= " ";
                                                    $arm_default_login_link .= "[ARMLINK]";
                                                    $arm_default_login_link .= __('Login', 'ARMember');
                                                    $arm_default_login_link .= "[/ARMLINK]";
                                                ?>

                                                <tr class="arm_login_link_options <?php echo ($show_login_link != '1') ? 'hidden_section' : ''; ?>">
                                                    <td colspan="2">
                                                        <label class="arm_form_opt_label"><?php _e('Login Link Label', 'ARMember'); ?>:</label>
                                                        <div class="arm_form_opt_input">
                                                            <input type="text" name="arm_form_settings[login_link_label]" value="<?php echo (!empty($form_settings['login_link_label'])) ? stripslashes($form_settings['login_link_label']) : $arm_default_login_link; ?>" class="login_link_label_input">
                                                            <span class="arm_info_text"><?php _e('To make partial part of sentence clickable, please use this pattern', 'ARMember'); ?> <strong>[ARMLINK]</strong>xx<strong>[/ARMLINK]</strong></span>
                                                        </div>
                                                        <div class="armclear"></div>
                                                        <div class="arm_form_opt_input">
                                                            <?php
                                                            $login_link_type = (isset($form_settings['login_link_type'])) ? $form_settings['login_link_type'] : 'page';
                                                            ?>
                                                            <label style="<?php echo (is_rtl()) ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?>">
                                                                <input type="radio" id="arm_login_link_type_modal" name="arm_form_settings[login_link_type]" value="modal" class="arm_login_link_type arm_iradio" <?php checked($login_link_type, 'modal'); ?>>
                                                                <span><?php _e('Modal', 'ARMember'); ?></span>
                                                            </label>
                                                            <label>
                                                                <input type="radio" id="arm_login_link_type_page" name="arm_form_settings[login_link_type]" value="page" class="arm_login_link_type arm_iradio" <?php checked($login_link_type, 'page'); ?>>
                                                                <span><?php _e('Redirect to Page', 'ARMember'); ?></span>
                                                            </label>
                                                            <div class="armclear"></div>
                                                            <div class="arm_login_link_type_option arm_login_link_type_option_modal <?php echo ($login_link_type != 'modal') ? 'hidden_section' : ''; ?>">
                                                                <?php
                                                                $defaultLoginForm = $arm_member_forms->arm_get_default_form_id('Login');
                                                                $loginFormsList = $arm_member_forms->arm_get_member_forms_by_type('Login');
                                                                $login_link_type_modal = (isset($form_settings['login_link_type_modal'])) ? $form_settings['login_link_type_modal'] : $defaultLoginForm;
                                                                $login_link_type_modal_form_type =  (isset($form_settings['login_link_type_modal_form_type'])) ? $form_settings['login_link_type_modal_form_type'] : 'arm_form';
                                                                ?>
                                                                <input type="hidden" id="login_link_type_modal_form_type" name="arm_form_settings[login_link_type_modal_form_type]" value="<?php echo $login_link_type_modal_form_type; ?>"/>

                                                                <input type="hidden" id="login_link_type_modal_form" name="arm_form_settings[login_link_type_modal]" value="<?php echo $login_link_type_modal; ?>"/>
                                                                <dl class="arm_selectbox column_level_dd arm_width_250 arm_margin_top_5">
                                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                    <dd>
                                                                        <ul data-id="login_link_type_modal_form">
                                                                            <?php if (!empty($loginFormsList)) { ?>
                                                                                <?php foreach ($loginFormsList as $mrform) { ?>
                                                                                    <li data-label="<?php echo $mrform['arm_form_label']; ?>" data-value="<?php echo $mrform['arm_form_id']; ?>" data-form_type='arm_form'><?php echo $mrform['arm_form_label']; ?></li>
                                                                                <?php } ?>
                                                                            <?php } ?>
                                                                        </ul>
                                                                    </dd>
                                                                </dl>
                                                            </div>
                                                            <div class="arm_login_link_type_option arm_login_link_type_option_page <?php echo ($login_link_type != 'page') ? 'hidden_section' : ''; ?>">
                                                                <?php
                                                                    $login_link_type_page = (isset($form_settings['login_link_type_page'])) ? $form_settings['login_link_type_page'] : $arm_global_settings->arm_get_single_global_settings('login_page_id', 0);
                                                                    $arm_global_settings->arm_wp_dropdown_pages(
                                                                        array(
                                                                            'selected' => $login_link_type_page,
                                                                            'name' => 'arm_form_settings[login_link_type_page]',
                                                                            'id' => 'login_link_type_page',
                                                                            'show_option_none' => 'Select Page',
                                                                            'option_none_value' => '',
                                                                            'class' => 'login_link_type_page',
                                                                        )
                                                                    );
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                <?php        
                                    }

                                ?>

                                <?php
                                $form_submit_action_type = !empty($form_settings['redirect_type']) ? $form_settings['redirect_type'] : 'page';
                                $f_redirect_page = (!empty($form_settings['redirect_page'])) ? $form_settings['redirect_page'] : $thank_you_page_id;
                                ?>
                                <?php if ($isRegister || $isEditProfile) { ?>
                                    <div class="arm_right_section_heading"><?php _e('Submit Action', 'ARMember'); ?></div>
                                <?php } else { ?>
                                    <div class="arm_right_section_heading"><?php _e('Login Form Options', 'ARMember'); ?></div>
                                <?php } ?>
                                <div class="arm_right_section_body arm_form_redirection_options arm_padding_bottom_15">
                                    <table class="arm_form_settings_style_block">
                                        <?php if (!$isRegister && !$isEditProfile) { ?>
                                            <?php $show_rememberme = (isset($form_settings['show_rememberme'])) ? $form_settings['show_rememberme'] : 0; ?>
                                            <tr>
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label" for="show_rememberme"><?php _e('Remember Me Checkbox', 'ARMember'); ?></label>
                                                    <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                        <input type="checkbox" id="show_rememberme" <?php checked($show_rememberme, '1'); ?> value="1" class="armswitch_input arm_show_rememberme_chk" name="arm_form_settings[show_rememberme]"/>
                                                        <label for="show_rememberme" class="armswitch_label"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php $show_registration_link = (isset($form_settings['show_registration_link'])) ? $form_settings['show_registration_link'] : 0; ?>
                                            <tr>
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label" for="show_registration_link"><?php _e('Display Registration Link?', 'ARMember'); ?></label>
                                                    <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                        <input type="checkbox" id="show_registration_link" <?php checked($show_registration_link, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[show_registration_link]"/>
                                                        <label for="show_registration_link" class="armswitch_label"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="arm_registration_link_options <?php echo ($show_registration_link != '1') ? 'hidden_section' : ''; ?>">
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label"><?php _e('Registration Link Label', 'ARMember'); ?>:</label>
                                                    <div class="arm_form_opt_input">
                                                        <input type="text" name="arm_form_settings[registration_link_label]" value="<?php echo (isset($form_settings['registration_link_label'])) ? stripslashes($form_settings['registration_link_label']) : __('Register', 'ARMember'); ?>" class="registration_link_label_input">
                                                        <span class="arm_info_text"><?php _e('To make partial part of sentence clickable, please use this pattern', 'ARMember'); ?> <strong>[ARMLINK]</strong>xx<strong>[/ARMLINK]</strong></span>
                                                    </div>
                                                    <div class="armclear"></div>
                                                    <div class="arm_form_opt_input">
                                                        <?php
                                                        $registration_link_type = (isset($form_settings['registration_link_type'])) ? $form_settings['registration_link_type'] : 'page';
                                                        ?>
                                                        <label style="<?php echo (is_rtl()) ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?>">
                                                            <input type="radio" id="arm_registration_link_type_modal" name="arm_form_settings[registration_link_type]" value="modal" class="arm_registration_link_type arm_iradio" <?php checked($registration_link_type, 'modal'); ?>>
                                                            <span><?php _e('Modal', 'ARMember'); ?></span>
                                                        </label>
                                                        <label>
                                                            <input type="radio" id="arm_registration_link_type_page" name="arm_form_settings[registration_link_type]" value="page" class="arm_registration_link_type arm_iradio" <?php checked($registration_link_type, 'page'); ?>>
                                                            <span><?php _e('Redirect to Page', 'ARMember'); ?></span>
                                                        </label>
                                                        <div class="armclear"></div>
                                                        <div class="arm_registration_link_type_option arm_registration_link_type_option_modal <?php echo ($registration_link_type != 'modal') ? 'hidden_section' : ''; ?>">
                                                            <?php
                                                            $defaultRegForm = $arm_member_forms->arm_get_default_form_id('registration');
                                                            $regFormsList = $arm_member_forms->arm_get_member_forms_by_type('registration');
                                                            $registration_link_type_modal = (isset($form_settings['registration_link_type_modal'])) ? $form_settings['registration_link_type_modal'] : $defaultRegForm;
                                                            $setup_data = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `" . $ARMember->tbl_arm_membership_setup. "`", ARRAY_A);
                                                            $registration_link_type_modal_form_type =  (isset($form_settings['registration_link_type_modal_form_type'])) ? $form_settings['registration_link_type_modal_form_type'] : 'arm_form';
                                                            ?>
                                                            <input type="hidden" id="registration_link_type_modal_form_type" name="arm_form_settings[registration_link_type_modal_form_type]" value="<?php echo $registration_link_type_modal_form_type; ?>"/>

                                                            <input type="hidden" id="registration_link_type_modal_form" name="arm_form_settings[registration_link_type_modal]" value="<?php echo $registration_link_type_modal; ?>"/>
                                                            <dl class="arm_selectbox">
                                                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul data-id="registration_link_type_modal_form">
                                                                        <?php if (!empty($regFormsList)) { ?>
                                                                            <?php foreach ($regFormsList as $mrform) { ?>
                                                                                <li data-label="<?php echo $mrform['arm_form_label']; ?>" data-value="<?php echo $mrform['arm_form_id']; ?>" data-form_type='arm_form'><?php echo $mrform['arm_form_label']; ?></li>
                                                                            <?php } ?>
                                                                        <?php } ?>


                                                                        <?php if(!empty($setup_data)){ ?>
                                                                            <?php foreach ($setup_data as $arm_setup) { ?>
                                                                                <li data-label="<?php echo $arm_setup['arm_setup_name']; ?>" data-value="<?php echo $arm_setup['arm_setup_id']; ?>" data-form_type='arm_setup'><?php echo $arm_setup['arm_setup_name']; ?></li>
                                                                            <?php } ?>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                        <div class="arm_registration_link_type_option arm_registration_link_type_option_page <?php echo ($registration_link_type != 'page') ? 'hidden_section' : ''; ?>">
                                                            <?php
                                                            $registration_link_type_page = (isset($form_settings['registration_link_type_page'])) ? $form_settings['registration_link_type_page'] : $arm_global_settings->arm_get_single_global_settings('register_page_id', 0);
                                                            $arm_global_settings->arm_wp_dropdown_pages(
                                                                    array(
                                                                        'selected' => $registration_link_type_page,
                                                                        'name' => 'arm_form_settings[registration_link_type_page]',
                                                                        'id' => 'registration_link_type_page',
                                                                        'show_option_none' => 'Select Page',
                                                                        'option_none_value' => '',
                                                                        'class' => 'registration_link_type_page',
                                                                    )
                                                            );
                                                            ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php $show_forgot_password_link = (isset($form_settings['show_forgot_password_link'])) ? $form_settings['show_forgot_password_link'] : 0; ?>
                                            <tr>
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label" for="show_forgot_password_link"><?php _e('Display Forgot Password Link?', 'ARMember'); ?></label>
                                                    <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                        <input type="checkbox" id="show_forgot_password_link" <?php checked($show_forgot_password_link, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[show_forgot_password_link]"/>
                                                        <label for="show_forgot_password_link" class="armswitch_label"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="arm_forgot_password_link_options <?php echo ($show_forgot_password_link != '1') ? 'hidden_section' : ''; ?>">
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label"><?php _e('Forgot Password Link Label', 'ARMember'); ?>:</label>
                                                    <div class="arm_form_opt_input">
                                                        <input type="text" name="arm_form_settings[forgot_password_link_label]" value="<?php echo (isset($form_settings['forgot_password_link_label'])) ? stripslashes($form_settings['forgot_password_link_label']) : __('Forgot Password', 'ARMember'); ?>" class="forgot_password_link_label_input">
                                                        <span class="arm_info_text"><?php _e('To make partial part of sentence clickable, please use this pattern', 'ARMember'); ?> <strong>[ARMLINK]</strong>xx<strong>[/ARMLINK]</strong></span>
                                                    </div>
                                                    <div class="armclear"></div>
                                                    <div class="arm_form_opt_input">
                                                        <?php
                                                        $forgot_password_link_type = (isset($form_settings['forgot_password_link_type'])) ? $form_settings['forgot_password_link_type'] : 'modal';
                                                        ?>
                                                        <label style="<?php echo (is_rtl()) ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?>">
                                                            <input type="radio" id="arm_forgot_password_link_type_modal" name="arm_form_settings[forgot_password_link_type]" value="modal" class="arm_forgot_password_link_type arm_iradio" <?php checked($forgot_password_link_type, 'modal'); ?>>
                                                            <span><?php _e('Modal', 'ARMember'); ?></span>
                                                        </label>
                                                        <label>
                                                            <input type="radio" id="arm_forgot_password_link_type_page" name="arm_form_settings[forgot_password_link_type]" value="page" class="arm_forgot_password_link_type arm_iradio" <?php checked($forgot_password_link_type, 'page'); ?>>
                                                            <span><?php _e('Redirect to Page', 'ARMember'); ?></span>
                                                        </label>
                                                        <div class="armclear"></div>
                                                        <div class="arm_forgot_password_link_type_option arm_forgot_password_link_type_option_page <?php echo ($forgot_password_link_type != 'page') ? 'hidden_section' : ''; ?>">
                                                            <?php
                                                            $forgot_password_link_type_page = (isset($form_settings['forgot_password_link_type_page'])) ? $form_settings['forgot_password_link_type_page'] : $arm_global_settings->arm_get_single_global_settings('forgot_password_page_id', 0);
                                                            ;
                                                            $arm_global_settings->arm_wp_dropdown_pages(
                                                                    array(
                                                                        'selected' => $forgot_password_link_type_page,
                                                                        'name' => 'arm_form_settings[forgot_password_link_type_page]',
                                                                        'id' => 'forgot_password_link_type_page',
                                                                        'show_option_none' => 'Select Page',
                                                                        'option_none_value' => '',
                                                                        'class' => 'forgot_password_link_type_page',
                                                                    )
                                                            );
                                                            ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php $arm_show_other_form_captcha_field = (isset($form_settings['show_other_form_captcha_field'])) ? $form_settings['show_other_form_captcha_field'] : 0; 
                                            $arm_recaptcha_key_status=1;
                                            $arm_recaptcha_site_key = (isset($all_global_settings['arm_recaptcha_site_key']) && !empty($all_global_settings['arm_recaptcha_site_key'])) ? $all_global_settings['arm_recaptcha_site_key'] : '';
                                            $arm_recaptcha_private_key = (isset($all_global_settings['arm_recaptcha_private_key']) && !empty($all_global_settings['arm_recaptcha_private_key'])) ? $all_global_settings['arm_recaptcha_private_key'] : '';
                                            
                                        } ?>
                                        <?php if ($isRegister) { ?>
                                            <?php $auto_login = (isset($form_settings['auto_login'])) ? $form_settings['auto_login'] : 0; ?>
                                            <tr>
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label" for="arm_auto_login_btn"><?php _e('Automatic login on signup', 'ARMember'); ?></label>
                                                    <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                        <input type="checkbox" id="arm_auto_login_btn" <?php checked($auto_login, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[auto_login]"/>
                                                        <label for="arm_auto_login_btn" class="armswitch_label"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->general_settings . '&action=redirection_options'); ?>" class="arm_configure_submission_redirection_link armemailaddbtn" target="_balnk" ><?php _e('Configure Submission Redirection', 'ARMember'); ?></a>
                                                </td>
                                            </tr>
                                        <?php }
                                        if( $isEditProfile ) { ?>
                                            <tr class="arm_edit_profile_link_options">
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label" for="arm_success_message"><?php esc_html_e('Message after successful submission', 'ARMember'); ?></label>
                                                    <div class="arm_form_opt_input">
                                                        <input type="text" name="arm_form_settings[edit_success_message]" value="<?php echo (isset($form_settings['edit_success_message'])) ? stripslashes($form_settings['edit_success_message']) : __('Your profile has been updated successfully', 'ARMember'); ?>" class="form_submit_action_input">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="arm_edit_profile_link_options">
                                                <?php
                                                    $view_profile = isset( $form_settings['view_profile_link'] ) ? $form_settings['view_profile_link'] : 0;
                                                ?>
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label" for="arm_view_profile_link"><?php _e('Display view profile link', 'ARMember'); ?></label>
                                                    <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                        <input type="checkbox" id="arm_view_profile_link" <?php checked( $view_profile, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[view_profile_link]" />
                                                        <label for="arm_view_profile_link" class="armswitch_label"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="arm_edit_profile_link_options" id="arm_view_profile_link_label" style="<?php echo ( 1 != $view_profile ) ? 'display:none;' : ''; ?>">
                                                <td colspan="2">
                                                    <label class="arm_form_opt_label" for="view_profile_link_label"><?php esc_html_e( 'Label for View Profile Link', 'ARMember' ); ?></label>
                                                    <div class="arm_form_opt_input">
                                                        <input type="text" name="arm_form_settings[arm_view_profile_link_label]" value="<?php echo ( isset( $form_settings['view_profile_link_label'] ) ? stripslashes( $form_settings['view_profile_link_label'] ) : __('View Profile', 'ARMember') ); ?>" class="form_submit_action_input" />
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                    <div class="armclear"></div>
                                </div>


                                <div class="arm_right_section_heading"><?php _e('Hidden Fields', 'ARMember'); ?></div>
                                <div class="arm_right_section_body arm_padding_bottom_15">
                                    <table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
                                        <tr>
                                            <td colspan="3">
                                                <label class="arm_form_opt_label" for="arm_enable_hidden_field"><?php _e('Enable Hidden Fields?', 'ARMember'); ?></label>
                                                <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                    <input type="checkbox" id="arm_enable_hidden_field" <?php checked($form_settings['is_hidden_fields'], '1'); ?> value="1" class="armswitch_input armIgnore" name="arm_form_settings[is_hidden_fields]"/>
                                                    <label for="arm_enable_hidden_field" class="armswitch_label"></label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="arm_form_hidden_field_options <?php echo ($form_settings['is_hidden_fields'] == 1) ? '' : 'hidden_section'; ?>">
                                            <td colspan="3">
                                                <ol class="arm_form_hidden_field_wrapper">
                                                    <?php
                                                    $totalField = 1;
                                                    if (!isset($form_settings['hidden_fields']) || empty($form_settings['hidden_fields'])) {
                                                        $form_settings['hidden_fields'][1] = array(
                                                            'title' => '',
                                                            'meta_key' => '',
                                                            'value' => '',
                                                        );
                                                    }
                                                    if (isset($form_settings['hidden_fields']) && !empty($form_settings['hidden_fields'])) {
                                                        foreach ($form_settings['hidden_fields'] as $hkey => $hval) {
                                                            ?>
                                                            <li class="arm_form_hidden_field" id="arm_form_hidden_field<?php echo $totalField; ?>">
                                                                <a href="javascript:void(0)" class="arm_remove_hidden_field" data-index="<?php echo $totalField; ?>"><img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_close_icon.png'; ?>" onmouseover="this.src='<?php echo MEMBERSHIP_IMAGES_URL.'/arm_close_icon_hover.png'; ?>'" onmouseout="this.src='<?php echo MEMBERSHIP_IMAGES_URL.'/arm_close_icon.png'; ?>'"></a>
                                                                
                                                                <div class="armclear"></div>
                                                                <span><?php _e('Title', 'ARMember'); ?></span>
                                                                <input type="text" name="arm_form_settings[hidden_fields][<?php echo $totalField; ?>][title]" class="arm_form_setting_input armIgnore" value="<?php echo (isset($hval['title'])) ? $hval['title'] : ''; ?>">
                                                                <div class="armclear"></div>
                                                                <span><?php _e('Meta Key', 'ARMember'); ?></span>
                                                                <input type="text" name="arm_form_settings[hidden_fields][<?php echo $totalField; ?>][meta_key]" class="arm_form_setting_input armIgnore" value="<?php echo (isset($hval['meta_key'])) ? $hval['meta_key'] : ''; ?>">
                                                                <div class="armclear"></div>
                                                                <span><?php _e('Meta Value', 'ARMember'); ?></span>
                                                                <input type="text" name="arm_form_settings[hidden_fields][<?php echo $totalField; ?>][value]" class="arm_form_setting_input armIgnore" value="<?php echo (isset($hval['value'])) ? $hval['value'] : ''; ?>">
                                                            </li>
                                                            <?php
                                                            $totalField++;
                                                        }
                                                    }
                                                    ?>
                                                </ol>
                                                <div class="arm_add_form_hidden_field_wrapper">
                                                    <a class="arm_add_hidden_field_link" id="arm_add_hidden_field_link" href="javascript:void(0)" data-field_index="<?php echo $totalField; ?>" data-img_url="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_close_icon.png'; ?>" data-hover_img_url="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_close_icon_hover.png'; ?>">+ <?php _e('Add More', 'ARMember'); ?></a>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>


                                <?php if (!$isRegister && !$isEditProfile) { ?>
                                    <?php if ($arm_social_feature->isSocialLoginFeature) { ?>
                                        <div class="arm_right_section_heading arm_form_special_section_heading"><?php _e('Social Connect Options', 'ARMember'); ?></div>
                                        <div class="arm_right_section_body arm_form_special_section_body arm_social_connect_options arm_padding_bottom_15">
                                            <table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
                                                <tr>
                                                    <td colspan="2">
                                                        <label class="arm_form_opt_label" for="enable_social_login"><?php _e('Enable Social Login?', 'ARMember'); ?></label>
                                                        <div class="armswitch arm_global_setting_switch arm_vertical_align_middle arm_margin_right_10" >
                                                            <input type="checkbox" id="enable_social_login" <?php checked($enable_social_login, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[enable_social_login]" data-configure="<?php echo (!empty($activeSocialNetworks)) ? '1' : '0'; ?>" data-configure_warning="<?php _e('Please configure one of social network and then try to ON this setting.', 'ARMember'); ?>"/>
                                                            <label for="enable_social_login" class="armswitch_label"></label>
                                                        </div>
                                                        <input type="hidden" id="arm_form_social_networks" value="<?php echo (implode(',', $social_networks)); ?>" name="arm_form_settings[social_networks]"/>
                                                        <input type="hidden" id="arm_form_social_networks_order" value="<?php echo (implode(',', $social_networks_order)); ?>" name="arm_form_settings[social_networks_order]"/>
                                                        <input type="hidden" id="arm_form_social_networks_settings" value='<?php echo (maybe_serialize($formSocialNetworksSettings)); ?>' name="arm_form_settings[social_networks_settings]"/>
                                                    </td>
                                                </tr>
                                                <tr class="arm_form_social_btn_options <?php echo ($enable_social_login != '1') ? 'hidden_section' : ''; ?>">
                                                    <td><label class="arm_form_opt_label"><?php _e('Button Position', 'ARMember'); ?></label></td>
                                                    <td>
                                                        <div class="arm_right">
                                                            <div class="arm_switch arm_social_btn_position_switch">
                                                                <label data-value="top" class="arm_switch_label <?php echo ($social_btn_position == 'top') ? 'active' : ''; ?>">&nbsp;&nbsp;<?php _e('Top', 'ARMember'); ?>&nbsp;</label>
                                                                <label data-value="bottom" class="arm_switch_label <?php echo ($social_btn_position == 'bottom') ? 'active' : ''; ?>"><?php _e('Bottom', 'ARMember'); ?></label>
                                                                <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][social_btn_position]" value="<?php echo $social_btn_position; ?>">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="arm_form_social_btn_options <?php echo ($enable_social_login != '1') ? 'hidden_section' : ''; ?>">
                                                    <td><label class="arm_form_opt_label"><?php _e('Button Type', 'ARMember'); ?></label></td>
                                                    <td>
                                                        <div class="arm_right">
                                                            <div class="arm_switch arm_social_btn_type_switch">
                                                                <label data-value="horizontal" class="arm_switch_label <?php echo ($social_btn_type == 'horizontal') ? 'active' : ''; ?>"><?php _e('Horizontal', 'ARMember'); ?></label>
                                                                <label data-value="vertical" class="arm_switch_label <?php echo ($social_btn_type == 'vertical') ? 'active' : ''; ?>"><?php _e('Vertical', 'ARMember'); ?></label>
                                                                <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][social_btn_type]" value="<?php echo $social_btn_type; ?>">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="arm_form_social_btn_options <?php echo ($enable_social_login != '1') ? 'hidden_section' : ''; ?>">
                                                    <td><label class="arm_form_opt_label"><?php _e('Button Align', 'ARMember'); ?></label></td>
                                                    <td>
                                                        <div class="arm_right">
                                                            <div class="arm_switch arm_switch3 arm_social_btn_align_switch">
                                                                <label data-value="left" class="arm_switch_label <?php echo ($social_btn_align == 'left') ? 'active' : ''; ?>"><?php _e('Left', 'ARMember'); ?></label>
                                                                <label data-value="center" class="arm_switch_label <?php echo ($social_btn_align == 'center') ? 'active' : ''; ?>"><?php _e('Center', 'ARMember'); ?></label>
                                                                <label data-value="right" class="arm_switch_label <?php echo ($social_btn_align == 'right') ? 'active' : ''; ?>"><?php _e('Right', 'ARMember'); ?></label>
                                                                <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][social_btn_align]" value="<?php echo $social_btn_align; ?>">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="arm_form_social_btn_options <?php echo ($enable_social_login != '1') ? 'hidden_section' : ''; ?>">
                                                    <td><label class="arm_form_opt_label"><?php _e('Button Skin', 'ARMember'); ?></label></td>
                                                    <td>
                                                        <div class="arm_right">
                                                            <a href="javascript:void(0)" class="arm_change_social_login_options" id="arm_change_social_login_options"><img src="<?php echo MEMBERSHIP_IMAGES_URL; ?>/change_social_skin.png" alt="<?php _e('Change Skin', 'ARMember'); ?>"/></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="arm_form_social_btn_options <?php echo ($enable_social_login != '1') ? 'hidden_section' : ''; ?>">
                                                    <td><label class="arm_form_opt_label"><?php _e('Separator', 'ARMember'); ?></label></td>
                                                    <td>
                                                        <div class="arm_right">
                                                            <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                                <input type="checkbox" id="enable_social_btn_separator" <?php checked($enable_social_btn_separator, '1'); ?> value="1" class="armswitch_input"name="arm_form_settings[style][enable_social_btn_separator]"/>
                                                                <label for="enable_social_btn_separator" class="armswitch_label"></label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="arm_form_social_btn_options arm_social_btn_separator_option <?php echo ($enable_social_login != '1') ? 'hidden_section' : ''; ?> <?php echo ($enable_social_btn_separator != '1') ? 'hidden_section2' : ''; ?>">
                                                    <td colspan="2">
                                                        <label class="arm_form_opt_label"><?php _e('Separator Text', 'ARMember'); ?>:</label>
                                                        <div class="arm_form_opt_input">
                                                            <input type="text" class="form_submit_action_input arm_social_btn_separator_input" name="arm_form_settings[style][social_btn_separator]" value="<?php echo stripslashes($social_btn_separator); ?>">
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    <?php } ?>
                                    <?php
                                    $changePassMsg = isset($otherFormIDs['change_password']['arm_form_settings']['message']) ? stripslashes($otherFormIDs['change_password']['arm_form_settings']['message']) : __('Your password has been changed successfully.', 'ARMember');
                                    $forgotgePassMsg = isset($otherFormIDs['forgot_password']['arm_form_settings']['message']) ? stripslashes($otherFormIDs['forgot_password']['arm_form_settings']['message']) : __('We have send you password reset link, Please check your mail.', 'ARMember');
                                    $forgotgePassDesc = isset($otherFormIDs['forgot_password']['arm_form_settings']['description']) ? stripslashes($otherFormIDs['forgot_password']['arm_form_settings']['description']) : '';
                                    ?>

                                    <div class="arm_right_section_heading"><?php _e('Google reCAPTCHA (V3)', 'ARMember'); ?></div>
                                    <div class="arm_right_section_body">
                                        <table class="arm_form_settings_style_block">

                                                <?php if ($arm_recaptcha_site_key == '' || $arm_recaptcha_private_key == '') {
                                                    $arm_recaptcha_key_status=0;
                                                } ?>

                                                <tr>
                                                    <td colspan="2">
                                                        <label class="arm_form_opt_label" for="arm_show_captcha_field"><?php _e('Enable Google Recaptcha', 'ARMember'); ?></label>
                                                        <div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
                                                            <input type="checkbox" id="arm_show_captcha_field" <?php checked($arm_show_other_form_captcha_field, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[show_other_form_captcha_field]"/>
                                                            <label for="arm_show_captcha_field" class="armswitch_label"></label>
                                                            <input type="hidden" name="arm_recaptcha_key_status" id="arm_recaptcha_key_status" value="<?php echo $arm_recaptcha_key_status;?>">
                                                        </div>
                                                        <div class="arm_recaptchav3_msg" style="<?php if($arm_show_other_form_captcha_field==1 && $arm_recaptcha_key_status==0){?>display:block;<?php }else{ ?>display:none;<?php }?>"><?php _e('Please setup site key and private key in General Settings otherwise recaptcha will not appear', 'ARMember'); ?></div>
                                                        
                                                    </td>
                                                </tr>
                                        </table>    
                                    </div>

                                    <div class="arm_right_section_heading"><?php _e('Messages', 'ARMember'); ?></div>
                                    <div class="arm_right_section_body arm_form_redirection_options arm_padding_bottom_15">
                                        <table class="arm_form_settings_style_block">
                                            <tr>
                                                <td colspan="2">
                                                    <span class="arm_form_opt_label"><?php _e('Forgot Password Messages', 'ARMember'); ?>:</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="arm_form_opt_input">
                                                        <label class="arm_form_opt_label"><?php _e('Form Description', 'ARMember'); ?>:</label>

                                                        <?php /*<input type="text" name="arm_form_settings[forgot_password][description]" style="max-width:auto" value="<?php echo addslashes($forgotgePassDesc); ?>" id="arm_forgot_password_description_input" class="arm_forgot_password_description_input form_submit_action_input">*/?>
                                                        <textarea name="arm_form_settings[forgot_password][description]" id="arm_forgot_password_description_input" class="arm_forgot_password_description_input form_submit_action_input" rows="2" cols="35"><?php echo $forgotgePassDesc; ?></textarea>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="arm_form_opt_input">
                                                        <label class="arm_form_opt_label"><?php _e('Display message after form submit', 'ARMember'); ?>:</label>
                                                        <input type="text" name="arm_form_settings[forgot_password][message]" value="<?php echo $forgotgePassMsg; ?>" id="form_submit_action_message_fp" class="form_submit_action_input">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <span class="arm_form_opt_label"><?php _e('Change Password Messages', 'ARMember'); ?>:</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="arm_form_opt_input">
                                                        <label class="arm_form_opt_label"><?php _e('Display message after form submit', 'ARMember'); ?>:</label>
                                                        <input type="text" name="arm_form_settings[change_password][message]" value="<?php echo $changePassMsg; ?>" id="form_submit_action_message_cp" class="form_submit_action_input">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                <?php } ?>
                                <div class="armclear"></div>
                                <?php $displayMapFields = false; ?>
                                <?php if ((!empty($email_tools) || ($opt_ins_feature == 1 && ( is_plugin_active('myMail/myMail.php') || is_plugin_active('mailster/mailster.php') )) || $opt_ins_feature == 1) && $isRegister ){
                                    $arm_opt_ins_cl_mode = (isset($form_settings['arm_opt_ins_cl_mode'])) ? $form_settings['arm_opt_ins_cl_mode'] : 0;
                                    $arm_opt_ins_cl_form_field = !empty($form_settings['arm_opt_ins_cl_form_field']) ? $form_settings['arm_opt_ins_cl_form_field'] : '';
                                    $arm_opt_ins_cl_optr = !empty($form_settings['arm_opt_ins_cl_optr']) ? $form_settings['arm_opt_ins_cl_optr'] : 'equal';
                                    $arm_opt_ins_cl_val = !empty($form_settings['arm_opt_ins_cl_val']) ? $form_settings['arm_opt_ins_cl_val'] : '';
                                ?>
                                    <div class="arm_right_section_heading arm_form_special_section_heading"><?php _e('Opt-ins', 'ARMember'); ?></div>
                                    <div class="arm_opt_ins_cl_wrapper">
                                        <div class="arm_opt_ins_cl_switch">
                                            <label class="arm_form_opt_label" for="arm_opt_ins_cl_mode"><?php _e('Conditional Subscription', 'ARMember'); ?></label>
                                            <div class="armswitch arm_global_setting_switch arm_vertical_align_middle" >
                                                <input type="checkbox" id="arm_opt_ins_cl_mode" <?php checked($arm_opt_ins_cl_mode, '1'); ?> value="1" class="armswitch_input" name="arm_form_settings[arm_opt_ins_cl_mode]"/>
                                                <label for="arm_opt_ins_cl_mode" class="armswitch_label"></label>
                                            </div>
                                        </div>
                                        <div class="arm_opt_ins_cl_form_fields_wrapper <?php echo ($arm_opt_ins_cl_mode == 1) ? '' : 'hidden_section'; ?>">
                                            <div>
                                                <span><?php _e('Subscribe If', 'ARMember'); ?> </span>
                                            </div>
                                            <input type="hidden" id="arm_opt_ins_cl_form_field" name="arm_form_settings[arm_opt_ins_cl_form_field]" data-id="arm_opt_ins_cl_form_field" value="<?php echo $arm_opt_ins_cl_form_field; ?>" data-old_value="<?php echo $arm_opt_ins_cl_form_field; ?>"/>
                                            <dl class="arm_selectbox column_level_dd">
                                                <dt><span class="arm_opt_ins_cl_form_field_span"></span><input type="text" style="display:none;" value="" class="arm_autocomplete arm_opt_ins_cl_form_field_auto"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                <dd>
                                                    <ul data-id="arm_opt_ins_cl_form_field">
                                                        <li data-label="<?php _e('Select Field', 'ARMember'); ?>" data-value=""><?php _e('Select Field', 'ARMember'); ?></li>
                                                        <?php
                                                        foreach ($arm_form_fields_for_cl as $key => $value) {
                                                        ?>
                                                        <li data-label="<?php _e($value, 'ARMember'); ?>" data-value="<?php echo $key; ?>"><?php _e($value, 'ARMember'); ?></li>
                                                        <?php } ?>
                                                    </ul>
                                                </dd>
                                            </dl>

                                            <span><?php _e('is', 'ARMember'); ?> </span>
                                            <input type="hidden" id="arm_opt_ins_cl_optr" name="arm_form_settings[arm_opt_ins_cl_optr]" data-id="arm_opt_ins_cl_optr" value="<?php echo $arm_opt_ins_cl_optr; ?>" data-old_value="<?php echo $arm_opt_ins_cl_optr; ?>"/>
                                            <dl class="arm_selectbox column_level_dd arm_opt_ins_cl_optin_operators">
                                                <dt><span class="arm_opt_ins_cl_optr_span"></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                <dd>
                                                    <ul data-id="arm_opt_ins_cl_optr">
                                                        <li data-label="<?php _e('=', 'ARMember'); ?>" data-value="equal"><?php _e('=', 'ARMember'); ?></li>
                                                        <li data-label="<?php _e('!=', 'ARMember'); ?>" data-value="not_equal"><?php _e('!=', 'ARMember'); ?></li>
                                                        <li data-label="<?php _e('>', 'ARMember'); ?>" data-value="greater"><?php _e('>', 'ARMember'); ?></li>
                                                        <li data-label="<?php _e('<', 'ARMember'); ?>" data-value="less"><?php _e('<', 'ARMember'); ?></li>
                                                    </ul>
                                                </dd>
                                            </dl>

                                            <input type="text" id="arm_opt_ins_cl_val" name="arm_form_settings[arm_opt_ins_cl_val]" class="arm_opt_ins_cl_val_txt" data-id="arm_form_width1" value="<?php echo $arm_opt_ins_cl_val ?>" />
                                        </div>
                                    </div>
                                    <div class="arm_right_section_body arm_form_special_section_body arm_email_tools arm_padding_bottom_15">
                                        <?php foreach ($email_tools as $etool => $etsetting) { ?>
                                            <?php
                                            $etoolName = '';
                                            $etoolName = apply_filters('arm_opt_ins_display_name', $etool, $etoolName);
                                            if ($etool == 'aweber') {
                                                $etoolName = __('Aweber', 'ARMember');
                                            }
                                            if ($etool == 'mailchimp') {
                                                $etoolName = __('MailChimp', 'ARMember');
                                            }
                                            if ($etool == 'constant') {
                                                $etoolName = __('Constant Contact', 'ARMember');
                                            }
                                            if ($etool == 'getresponse') {
                                                $etoolName = __('Get Response', 'ARMember');
                                            }
                                            if ($etool == 'madmimi') {
                                                $etoolName = __('Mad Mimi', 'ARMember');
                                            }
                                            if ($etool == 'mailerlite') {
                                                $etoolName = __('Mailer Lite', 'ARMember');
                                            }
                                            if ($etool == 'sendinblue') {
                                                $etoolName = __('Send In Blue', 'ARMember');
                                            }
                                            
                                            $et_list_id = (isset($etsetting['list_id'])) ? $etsetting['list_id'] : '';
                                            $lists = (isset($etsetting['list'])) ? $etsetting['list'] : array();
                                            $list_id = (isset($form_settings['email'][$etool]['list_id'])) ? $form_settings['email'][$etool]['list_id'] : $et_list_id;
                                            $fetStatus = (isset($form_settings['email'][$etool]['status'])) ? $form_settings['email'][$etool]['status'] : 0;
                                            if (!$displayMapFields && $fetStatus == '1') {
                                                $displayMapFields = true;
                                            }
                                            ?>
                                            <?php if (isset($etsetting['status']) && $etsetting['status'] == '1') { ?>
                                                <div class="arm_etool_options_container">
                                                    <label>
                                                        <input type="checkbox" id="arm_etool_option_<?php echo $etool ?>" name="arm_form_settings[email][<?php echo $etool ?>][status]" value="1" class="arm_icheckbox arm_form_email_tool_radio" data-type="<?php echo $etool; ?>" <?php checked($fetStatus, '1'); ?>><label for="arm_etool_option_<?php echo $etool ?>"><?php echo $etoolName; ?></label>
                                                    </label>
                                                    <?php
                                                    $hide_section = true;
                                                    $hide_section = apply_filters('arm_hide_optin_list_selection', true, $etool);
                                                    if ($hide_section == true) {
                                                        ?>

                                                        <div class="arm_etool_list_container <?php echo ($fetStatus != 1) ? 'hidden_section' : ''; ?>">
                                                            <span><?php _e('List Name', 'ARMember'); ?>&nbsp;&nbsp;</span>
                                                            <input type="hidden" id="<?php echo $etool ?>_list_name" name="arm_form_settings[email][<?php echo $etool ?>][list_id]" value="<?php echo $list_id; ?>"/>
                                                            <dl class="arm_selectbox column_level_dd arm_width_150">
                                                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul data-id="<?php echo $etool ?>_list_name" id="arm_<?php echo $etool ?>_list">
                                                                        <?php
                                                                        if (!empty($lists)) { 
                                                                            foreach ($lists as $list) { ?>
                                                                                <li data-label="<?php echo $list['name']; ?>" data-value="<?php echo $list['id']; ?>"><?php echo $list['name']; ?></li>
                                                                            <?php 
                                                                            }
                                                                        } ?>
                                                                    </ul>
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                        <?php
                                                    } else {
                                                        do_action('arm_set_optin_list_selection', $etool, $form_settings);
                                                    }
                                                    ?>
                                                </div>
                                            <?php } ?>
                                            <?php
                                        }
                                        if (is_plugin_active('myMail/myMail.php') || is_plugin_active('mailster/mailster.php')) {
                                            $mymail_version = get_option('mymail_version');
                                            $mailster_version = get_option('mailster_version');
                                            $mail_title = (is_plugin_active('myMail/myMail.php')) ? __('myMail', 'ARMember') : __('Mailster', 'ARMember');
                                            
                                            if ($mymail_version >= "2.0.20" || $mailster_version >= "2.2") {
                                                if(version_compare($mailster_version, '2.3','<'))
                                                {
                                                    $all_mymail_lists = mymail('lists')->get();
                                                }
                                                else
                                                {
                                                    $all_mymail_lists = mailster('lists')->get();
                                                }

                                                $mymail_list_id = (isset($form_settings['email']['mymail']['list_id'])) ? $form_settings['email']['mymail']['list_id'] : '';
                                                $mymailStatus = (isset($form_settings['email']['mymail']['status'])) ? $form_settings['email']['mymail']['status'] : 0;
                                                ?>
                                                <div class="arm_etool_options_container">
                                                    <label>
                                                        <input type="checkbox" id="arm_etool_option_mymail" name="arm_form_settings[email][mymail][status]" value="1" class="arm_icheckbox arm_form_email_tool_radio" data-type="mymail" <?php checked($mymailStatus, '1'); ?>><label for="arm_etool_option_mymail"><?php echo $mail_title; ?></label>
                                                    </label>
                                                    <div class="arm_etool_list_container <?php echo ($mymailStatus != 1) ? 'hidden_section' : ''; ?>">
                                                        <span><?php _e('List Name', 'ARMember'); ?>&nbsp;&nbsp;</span>
                                                        <input type="hidden" id="mymail_list_name" name="arm_form_settings[email][mymail][list_id]" value="<?php echo $mymail_list_id; ?>"/>
                                                        <dl class="arm_selectbox column_level_dd arm_width_150">
                                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                            <dd>
                                                                <ul data-id="mymail_list_name" id="arm_mymail_list">
                                                                    <li data-label="<?php _e('Select List Name', 'ARMember'); ?>" data-value=""><?php _e('Select List Name', 'ARMember'); ?></li>
                                                                    <?php if (!empty($all_mymail_lists)) { ?>
                                                                        <?php foreach ($all_mymail_lists as $key => $format) { ?>
                                                                            <li data-label="<?php echo $format->name; ?>" data-value="<?php echo $format->ID; ?>"><?php echo $format->name; ?></li>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
					<?php  do_action('arm_add_on_opt_ins_options', $form_settings); ?>
                                        <div class="armclear"></div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div id="tabsetting-2" class="arm-tab-content">
                                <div class="arm_form_setting_options_head style_setting_main_heading"><?php _e('Style Settings', 'ARMember'); ?></div>
                                <div id="arm_form_styles_fields_container" class="arm_form_styles_fields_container" data-form_id="<?php echo $form_id; ?>">
                                    <div id="arm_accordion">
                                        <ul>
                                            <li class="arm_active_section">
                                                <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Form Options', 'ARMember'); ?>:<i></i></a>
                                                <div id="one" class="arm_accordion default">
                                                    <table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
                                                        <tr>
                                                            <td><?php _e('Form Style', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type="hidden" id="arm_manage_form_layout1" name="arm_form_settings[style][form_layout]" class="arm_manage_form_layout armMappedTextbox" data-id="arm_manage_form_layout" value="<?php echo $formLayout; ?>" data-old_value="<?php echo $formLayout; ?>"/>
                                                                    <dl class="arm_selectbox column_level_dd arm_width_160">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_manage_form_layout1">
                                                                                <li data-label="<?php _e('Standard Style', 'ARMember'); ?>" data-value="iconic"><?php _e('Standard Style', 'ARMember'); ?></li>
                                                                                <li data-label="<?php _e('Material Style', 'ARMember'); ?>" data-value="writer"><?php _e('Material Style', 'ARMember'); ?></li>
                                                                                <li data-label="<?php _e('Rounded Style', 'ARMember'); ?>" data-value="rounded"><?php _e('Rounded Style', 'ARMember'); ?></li>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Form Width', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type="text" id="arm_form_width" name="arm_form_settings[style][form_width]" class="arm_form_width arm_form_setting_input armMappedTextbox arm_width_130" data-id="arm_form_width1" value="<?php echo!empty($form_settings['style']['form_width']) ? $form_settings['style']['form_width'] : '600'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
                                                                    <input type='hidden' id="arm_form_width_type" name="arm_form_settings[style][form_width_type]" class="arm_form_width_type" value="px" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="arm_form_editor_field_label"><?php _e('Border', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='text' id="arm_form_border_width" name="arm_form_settings[style][form_border_width]" class="arm_form_width arm_form_setting_input arm_width_80" value="<?php echo isset($form_settings['style']['form_border_width']) ? $form_settings['style']['form_border_width'] : '0'; ?>" onkeydown="javascript:return checkNumber(event)" />

                                                                    <br />Width (px)
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='text' id="arm_form_border_radius" name="arm_form_settings[style][form_border_radius]" class="arm_form_width arm_form_setting_input arm_width_80" value="<?php echo isset($form_settings['style']['form_border_radius']) ? $form_settings['style']['form_border_radius'] : '8'; ?>" onkeydown="javascript:return checkNumber(event)" />

                                                                    <br />Radius (px)
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_form_border_style" name="arm_form_settings[style][form_border_style]" class="arm_form_border_style" value="<?php echo!empty($form_settings['style']['form_border_style']) ? $form_settings['style']['form_border_style'] : 'solid'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_150">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_form_border_style">
                                                                                <li data-label="Solid" data-value="solid">Solid</li>
                                                                                <li data-label="Dashed" data-value="dashed">Dashed</li>
                                                                                <li data-label="Dotted" data-value="dotted">Dotted</li>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                    <br />Style
                                                                </div>
                                                            </td>                                            
                                                        </tr>
                                                        <tr>
                                                            <td class="arm_form_editor_field_label"><?php _e('Form Padding', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_button_margin_inputs_container arm_right">
                                                                    <?php
                                                                    $form_settings['style']['form_padding_left'] = (is_numeric($form_settings['style']['form_padding_left'])) ? $form_settings['style']['form_padding_left'] : 20;
                                                                    $form_settings['style']['form_padding_top'] = (is_numeric($form_settings['style']['form_padding_top'])) ? $form_settings['style']['form_padding_top'] : 20;
                                                                    $form_settings['style']['form_padding_right'] = (is_numeric($form_settings['style']['form_padding_right'])) ? $form_settings['style']['form_padding_right'] : 20;
                                                                    $form_settings['style']['form_padding_bottom'] = (is_numeric($form_settings['style']['form_padding_bottom'])) ? $form_settings['style']['form_padding_bottom'] : 20;
                                                                    ?>
                                                                    <div class="arm_button_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[style][form_padding_left]" id="arm_form_padding_left" class="arm_form_padding_left" value="<?php echo $form_settings['style']['form_padding_left']; ?>"/>
                                                                        <br /><?php _e('Left', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_button_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[style][form_padding_top]" id="arm_form_padding_top" class="arm_form_padding_top" value="<?php echo $form_settings['style']['form_padding_top']; ?>"/>
                                                                        <br /><?php _e('Top', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_button_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[style][form_padding_right]" id="arm_form_padding_right" class="arm_form_padding_right" value="<?php echo $form_settings['style']['form_padding_right']; ?>"/>
                                                                        <br /><?php _e('Right', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_button_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[style][form_padding_bottom]" id="arm_form_padding_bottom" class="arm_form_padding_bottom" value="<?php echo $form_settings['style']['form_padding_bottom']; ?>"/>
                                                                        <br /><?php _e('Bottom', 'ARMember'); ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td class="arm_vertical_align_top"><?php _e('Background', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <?php
                                                                    $isFormBGImg = !empty($form_settings['style']['form_bg']) && file_exists(MEMBERSHIP_UPLOAD_DIR . '/' . basename($form_settings['style']['form_bg'])) ? true : false;
                                                                    $form_settings['style']['form_bg'] = ($isFormBGImg) ? $form_settings['style']['form_bg'] : '';
                                                                    ?>
                                                                    <div class="arm_form_bg_upload_wrapper">
                                                                        <div class="armFileUploadWrapper">
                                                                            <div class="armFileUploadContainer" style="<?php echo ($isFormBGImg) ? 'display: none;' : ''; ?>">
                                                                                <div class="armFileUpload-icon"></div><?php _e('Upload', 'ARMember'); ?>
                                                                                <input id="armFormBGFileUpload" class="armFileUpload armFormBGFileUpload armIgnore" name="arm_form_settings[style][form_bg_file]" type="file" value="" accept=".jpg,.jpeg,.png,.gif,.bmp" data-file_size="5"/>
                                                                            </div>
                                                                            <div class="armFileRemoveContainer" style="<?php echo ($isFormBGImg) ? 'display: inline-block;' : ''; ?>"><div class="armFileRemove-icon"></div><?php _e('Remove', 'ARMember'); ?></div>
                                                                            <div class="armUploadedFileName" id="armFormBGUploadedFileName"></div>
                                                                            <div class="armFileMessages" id="armFileUploadMsg"></div>
                                                                            <input class="arm_file_url" type="hidden" name="arm_form_settings[style][form_bg]" value="<?php echo $form_settings['style']['form_bg']; ?>">
                                                                            <div class="arm_image_file_preview"><?php
                                                                                if ($isFormBGImg) {
                                                                                    echo '<img alt="" src="' . $form_settings['style']['form_bg'] . '"/>';
                                                                                }
                                                                                ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Opacity', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_form_opacity" name="arm_form_settings[style][form_opacity]" class="arm_form_opacity" value="<?php echo!empty($form_settings['style']['form_opacity']) ? $form_settings['style']['form_opacity'] : '1'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_80 arm_min_width_50">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_form_opacity">
                                                                                <li data-label="1.0" data-value="1">1.0</li>
                                                                                <li data-label="0.9" data-value="0.9">0.9</li>
                                                                                <li data-label="0.8" data-value="0.8">0.8</li>
                                                                                <li data-label="0.7" data-value="0.7">0.7</li>
                                                                                <li data-label="0.6" data-value="0.6">0.6</li>
                                                                                <li data-label="0.5" data-value="0.5">0.5</li>
                                                                                <li data-label="0.4" data-value="0.4">0.4</li>
                                                                                <li data-label="0.3" data-value="0.3">0.3</li>
                                                                                <li data-label="0.2" data-value="0.2">0.2</li>
                                                                                <li data-label="0.1" data-value="0.1">0.1</li>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2" class="font_settings_label"><?php _e('Form Title Settings', 'ARMember'); ?></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Hide Title', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <div class="armswitch arm_global_setting_switch arm_vertical_align_middle" >
                                                                        <input type="checkbox" id="arm_hide_form_title" <?php checked($form_settings['hide_title'], '1'); ?> value="1" class="armswitch_input armIgnore" name="arm_form_settings[hide_title]"/>
                                                                        <label for="arm_hide_form_title" class="armswitch_label"></label>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Family', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_form_title_font_family" name="arm_form_settings[style][form_title_font_family]" class="arm_form_title_font_family" value="<?php echo!empty($form_settings['style']['form_title_font_family']) ? $form_settings['style']['form_title_font_family'] : 'Helvetica'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_150">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_form_title_font_family">
                                                                                <?php echo $arm_member_forms->arm_fonts_list(); ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Size', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_form_title_font_size" name="arm_form_settings[style][form_title_font_size]" class="arm_form_title_font_size" value="<?php echo isset($form_settings['style']['form_title_font_size']) ? $form_settings['style']['form_title_font_size'] : '26'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_120">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_form_title_font_size">
                                                                                <?php
                                                                                for ($i = 8; $i < 41; $i++) {
                                                                                    ?><li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li><?php
                                                                                }
                                                                                ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                    <span>(px)</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Style', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <div class="arm_font_style_options">
                                                                        <!--/. Font Bold Option ./-->
                                                                        <label class="arm_font_style_label <?php echo ($form_settings['style']['form_title_font_bold'] == '1') ? 'arm_style_active' : ''; ?>" data-value="bold" data-field="arm_form_title_font_bold"><i class="armfa armfa-bold"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][form_title_font_bold]" id="arm_form_title_font_bold" class="arm_form_title_font_bold" value="<?php echo $form_settings['style']['form_title_font_bold']; ?>" />
                                                                        <!--/. Font Italic Option ./-->
                                                                        <label class="arm_font_style_label <?php echo ($form_settings['style']['form_title_font_italic'] == '1') ? 'arm_style_active' : ''; ?>" data-value="italic" data-field="arm_form_title_font_italic"><i class="armfa armfa-italic"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][form_title_font_italic]" id="arm_form_title_font_italic" class="arm_form_title_font_italic" value="<?php echo $form_settings['style']['form_title_font_italic']; ?>" />
                                                                        <!--/. Text Decoration Options ./-->
                                                                        <label class="arm_font_style_label arm_decoration_label <?php echo ($form_settings['style']['form_title_font_decoration'] == 'underline') ? 'arm_style_active' : ''; ?>" data-value="underline" data-field="arm_form_title_font_decoration"><i class="armfa armfa-underline"></i></label>
                                                                        <label class="arm_font_style_label arm_decoration_label <?php echo ($form_settings['style']['form_title_font_decoration'] == 'line-through') ? 'arm_style_active' : ''; ?>" data-value="line-through" data-field="arm_form_title_font_decoration"><i class="armfa armfa-strikethrough"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][form_title_font_decoration]" id="arm_form_title_font_decoration" class="arm_form_title_font_decoration" value="<?php echo $form_settings['style']['form_title_font_decoration']; ?>" />
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Title Position', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <?php $form_settings['style']['form_title_position'] = (!empty($form_settings['style']['form_title_position'])) ? $form_settings['style']['form_title_position'] : 'left'; ?>
                                                                    <div class="arm_switch arm_switch3 arm_form_title_position_switch">
                                                                        <label data-value="left" class="arm_switch_label <?php echo ($form_settings['style']['form_title_position'] == 'left') ? 'active' : ''; ?>"><?php _e('Left', 'ARMember'); ?></label>
                                                                        <label data-value="center" class="arm_switch_label <?php echo ($form_settings['style']['form_title_position'] == 'center') ? 'active' : ''; ?>"><?php _e('Center', 'ARMember'); ?></label>
                                                                        <label data-value="right" class="arm_switch_label <?php echo ($form_settings['style']['form_title_position'] == 'right') ? 'active' : ''; ?>"><?php _e('Right', 'ARMember'); ?></label>
                                                                        <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][form_title_position]" value="<?php echo $form_settings['style']['form_title_position']; ?>">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>

                                                        <tr class="arm_validation_message_type_container <?php echo ($formLayout == 'writer') ? 'hidden_section' : ''; ?>">
                                                            <td colspan="3"><?php _e('Validation Message Type', 'ARMember'); ?></td>
                                                        </tr>
                                                        <tr class="arm_validation_message_type_container <?php echo ($formLayout == 'writer') ? 'hidden_section' : ''; ?>">
                                                            <td colspan="3">
                                                                <?php $msg_validation_type = $form_settings['style']['validation_type'] = (!empty($form_settings['style']['validation_type'])) ? $form_settings['style']['validation_type'] : 'modern'; ?>
                                                                <div class="arm_switch arm_switch2 arm_validation_style_switch">
                                                                    <label data-value="modern" class="arm_switch_label <?php echo ($form_settings['style']['validation_type'] == 'modern') ? 'active' : ''; ?>"><?php _e('Modern', 'ARMember'); ?></label>
                                                                    <label data-value="standard" class="arm_switch_label <?php echo ($form_settings['style']['validation_type'] == 'standard') ? 'active' : ''; ?>"><?php _e('Standard', 'ARMember'); ?></label>
                                                                    <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][validation_type]" value="<?php echo $form_settings['style']['validation_type']; ?>">
                                                                </div>
                                                            </td>
                                                        </tr>

                                                        <tr class="arm_validation_message_position_container <?php echo ($formLayout == 'writer' || $msg_validation_type == 'standard') ? 'hidden_section' : ''; ?>">
                                                            <td colspan="3"><?php _e('Validation Message Position', 'ARMember'); ?></td>
                                                        </tr>
                                                        <tr class="arm_validation_message_position_container <?php echo ($formLayout == 'writer' || $msg_validation_type == 'standard') ? 'hidden_section' : ''; ?>">
                                                            <td colspan="3">
                                                                <?php $form_settings['style']['validation_position'] = (!empty($form_settings['style']['validation_position'])) ? $form_settings['style']['validation_position'] : 'bottom'; ?>
                                                                <div class="arm_switch arm_switch4 arm_validation_position_switch">
                                                                    <label data-value="top" class="arm_switch_label <?php echo ($form_settings['style']['validation_position'] == 'top') ? 'active' : ''; ?>"><?php _e('Top', 'ARMember'); ?></label>
                                                                    <label data-value="bottom" class="arm_switch_label <?php echo ($form_settings['style']['validation_position'] == 'bottom') ? 'active' : ''; ?>"><?php _e('Bottom', 'ARMember'); ?></label>
                                                                    <label data-value="left" class="arm_switch_label <?php echo ($form_settings['style']['validation_position'] == 'left') ? 'active' : ''; ?>"><?php _e('Left', 'ARMember'); ?></label>
                                                                    <label data-value="right" class="arm_switch_label <?php echo ($form_settings['style']['validation_position'] == 'right') ? 'active' : ''; ?>"><?php _e('Right', 'ARMember'); ?></label>
                                                                    <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][validation_position]" value="<?php echo $form_settings['style']['validation_position']; ?>">
                                                                </div>
                                                            </td>
                                                        </tr>

                                                        <tr class="arm_registration_link_options <?php echo ($show_registration_link != '1') ? 'hidden_section' : ''; ?>">
                                                            <td colspan="2" class="font_settings_label"><?php _e('Link Position Settings', 'ARMember'); ?></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr class="arm_registration_link_options <?php echo ($show_registration_link != '1') ? 'hidden_section' : ''; ?>">
                                                            <td class="arm_form_editor_field_label"><?php _e('Registration Link Margin', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <?php
                                                                $registration_link_margin = (isset($form_settings['registration_link_margin'])) ? $form_settings['registration_link_margin'] : array();
                                                                $registration_link_margin['left'] = (isset($registration_link_margin['left']) && is_numeric($registration_link_margin['left'])) ? $registration_link_margin['left'] : 0;
                                                                $registration_link_margin['top'] = (isset($registration_link_margin['top']) && is_numeric($registration_link_margin['top'])) ? $registration_link_margin['top'] : 0;
                                                                $registration_link_margin['right'] = (isset($registration_link_margin['right']) && is_numeric($registration_link_margin['right'])) ? $registration_link_margin['right'] : 0;
                                                                $registration_link_margin['bottom'] = (isset($registration_link_margin['bottom']) && is_numeric($registration_link_margin['bottom'])) ? $registration_link_margin['bottom'] : 0;
                                                                ?>
                                                                <div class="arm_registration_link_margin_inputs_container">
                                                                    <div class="arm_registration_link_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[registration_link_margin][left]" id="arm_registration_link_margin_left" class="arm_registration_link_margin_left" value="<?php echo $registration_link_margin['left']; ?>"/>
                                                                        <br /><?php _e('Left', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_registration_link_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[registration_link_margin][top]" id="arm_registration_link_margin_top" class="arm_registration_link_margin_top" value="<?php echo $registration_link_margin['top']; ?>"/>
                                                                        <br /><?php _e('Top', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_registration_link_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[registration_link_margin][right]" id="arm_registration_link_margin_right" class="arm_registration_link_margin_right" value="<?php echo $registration_link_margin['right']; ?>"/>
                                                                        <br /><?php _e('Right', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_registration_link_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[registration_link_margin][bottom]" id="arm_registration_link_margin_bottom" class="arm_registration_link_margin_bottom" value="<?php echo $registration_link_margin['bottom']; ?>"/>
                                                                        <br /><?php _e('Bottom', 'ARMember'); ?>
                                                                    </div>
                                                                </div>
                                                                <div class="armclear"></div>
                                                            </td>
                                                        </tr>
                                                        <tr class="arm_forgot_password_link_options <?php echo ($show_forgot_password_link != '1') ? 'hidden_section' : ''; ?>">
                                                            <td class="arm_form_editor_field_label"><?php _e('Forgot Password Link Margin', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <?php
                                                                $forgot_password_link_margin = (isset($form_settings['forgot_password_link_margin'])) ? $form_settings['forgot_password_link_margin'] : array();
                                                                $forgot_password_link_margin['left'] = (isset($forgot_password_link_margin['left']) && is_numeric($forgot_password_link_margin['left'])) ? $forgot_password_link_margin['left'] : 0;
                                                                $forgot_password_link_margin['top'] = (isset($forgot_password_link_margin['top']) && is_numeric($forgot_password_link_margin['top'])) ? $forgot_password_link_margin['top'] : 0;
                                                                $forgot_password_link_margin['right'] = (isset($forgot_password_link_margin['right']) && is_numeric($forgot_password_link_margin['right'])) ? $forgot_password_link_margin['right'] : 0;
                                                                $forgot_password_link_margin['bottom'] = (isset($forgot_password_link_margin['bottom']) && is_numeric($forgot_password_link_margin['bottom'])) ? $forgot_password_link_margin['bottom'] : 0;
                                                                ?>
                                                                <div class="arm_forgot_password_link_margin_inputs_container">
                                                                    <div class="arm_forgot_password_link_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[forgot_password_link_margin][left]" id="arm_forgot_password_link_margin_left" class="arm_forgot_password_link_margin_left" value="<?php echo $forgot_password_link_margin['left']; ?>"/>
                                                                        <br /><?php _e('Left', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_forgot_password_link_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[forgot_password_link_margin][top]" id="arm_forgot_password_link_margin_top" class="arm_forgot_password_link_margin_top" value="<?php echo $forgot_password_link_margin['top']; ?>"/>
                                                                        <br /><?php _e('Top', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_forgot_password_link_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[forgot_password_link_margin][right]" id="arm_forgot_password_link_margin_right" class="arm_forgot_password_link_margin_right" value="<?php echo $forgot_password_link_margin['right']; ?>"/>
                                                                        <br /><?php _e('Right', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_forgot_password_link_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[forgot_password_link_margin][bottom]" id="arm_forgot_password_link_margin_bottom" class="arm_forgot_password_link_margin_bottom" value="<?php echo $forgot_password_link_margin['bottom']; ?>"/>
                                                                        <br /><?php _e('Bottom', 'ARMember'); ?>
                                                                    </div>
                                                                </div>
                                                                <div class="armclear"></div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </li>
                                            <li id="arm_color_scheme_container" class="arm_form_style_color_schemes">
                                                <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Color Options', 'ARMember'); ?>:<i></i></a>
                                                <div id="two" class="arm_accordion">
                                                    <table class="arm_form_settings_style_block">
                                                        <tr>
                                                            <td colspan="2">
                                                                <div class="c_schemes">
                                                                    <?php foreach ($formColorSchemes as $color => $color_opt) { ?>
                                                                        <?php if ($color != 'custom') { ?>
                                                                            <label class="arm_color_scheme_block arm_color_scheme_block_<?php echo $color; ?> <?php echo ($form_settings['style']['color_scheme'] == $color) ? 'arm_color_box_active' : ''; ?>" style="background-color:<?php echo isset($color_opt['main_color']) ? $color_opt['main_color'] : ''; ?>">
                                                                                <input id="arm_color_block_radio_<?php echo $color; ?>" type="radio" name="arm_form_settings[style][color_scheme]" value="<?php echo $color; ?>" class="arm_color_block_radio armMappedRadio" data-id="arm_color_block_radio_<?php echo $color; ?>1" <?php checked($form_settings['style']['color_scheme'], $color) ?>/>
                                                                            </label>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                    <label class="arm_color_scheme_block arm_color_scheme_block_custom">
                                                                        <span><?php _e('Custom Color', 'ARMember'); ?></span>
                                                                    </label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <div class="arm_form_custom_style_opts arm_slider_box arm_custom_scheme_box">
                                                        <div class="arm_form_field_settings_menu arm_slider_box_container arm_custom_scheme_container">
                                                            <div class="arm_slider_box_arrow arm_custom_scheme_arrow"></div>
                                                            <div class="arm_slider_box_heading" style="display: none;"><?php _e('Custom Setting', 'ARMember'); ?></div>
                                                            <div class="arm_slider_box_body arm_custom_scheme_block">
                                                                <?php
                                                                $formColorScheme = isset($form_settings['style']['color_scheme']) ? $form_settings['style']['color_scheme'] : 'blue';
                                                                $formColors = isset($formColorSchemes[$formColorScheme]) ? $formColorSchemes[$formColorScheme] : array();
                                                                ?>
                                                                <table class="arm_form_settings_style_block">
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_main_label" colspan="4"><?php _e('Form', 'ARMember'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_form_title_font_color" type="text" name="arm_form_settings[style][form_title_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['form_title_font_color']; ?>"/>
                                                                            <span><?php _e('Form Title', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_form_bg_color" type="text" name="arm_form_settings[style][form_bg_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['form_bg_color']; ?>"/>
                                                                            <span><?php _e('Form Background', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_form_border_color" type="text" name="arm_form_settings[style][form_border_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['form_border_color']; ?>"/>
                                                                            <span><?php _e('Form Border', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <?php if (!$isRegister && !$isEditProfile) { ?>
                                                                            <td class="arm_custom_scheme_sub_label">
                                                                                <input id="arm_login_link_font_color" type="text" name="arm_form_settings[style][login_link_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['login_link_font_color']; ?>"/>
                                                                                <span><?php _e('Forgot / Register Link', 'ARMember'); ?></span>
                                                                            </td>
                                                                        <?php } ?>


                                                                        <?php
                                                                            if($isRegister)
                                                                            {
                                                                        ?>
                                                                                <td class="arm_custom_scheme_sub_label">
                                                                                    <input id="arm_register_link_font_color" type="text" name="arm_form_settings[style][register_link_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo ($form_settings['style']['register_link_font_color']); ?>"/>
                                                                                    <span><?php _e('Register Link', 'ARMember'); ?></span>
                                                                                </td>
                                                                        <?php        
                                                                            }
                                                                        ?>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_divider" colspan="4"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_main_label" colspan="4"><?php _e('Label & Input Fields', 'ARMember'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_field_font_color" type="text" name="arm_form_settings[style][field_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['field_font_color']; ?>"/>
                                                                            <span><?php _e('Field Font', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_field_border_color" type="text" name="arm_form_settings[style][field_border_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['field_border_color']; ?>"/>
                                                                            <span><?php _e('Field Border', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_field_focus_color" type="text" name="arm_form_settings[style][field_focus_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['field_focus_color']; ?>" data-form_id="<?php echo $form_id; ?>"/>
                                                                            <span><?php _e('Field Focus', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td class="arm_custom_scheme_sub_label arm_custom_scheme_sub_label_no_writer <?php echo ($formLayout == 'writer') ? 'hidden_section' : ''; ?>">
                                                                            <input id="arm_field_bg_color" type="text" name="arm_form_settings[style][field_bg_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['field_bg_color']; ?>" data-form_id="<?php echo $form_id; ?>"/>
                                                                            <span><?php _e('Field Background', 'ARMember'); ?></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_lable_font_color" type="text" name="arm_form_settings[style][lable_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['lable_font_color']; ?>"/>
                                                                            <span><?php _e('Label Font', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_divider" colspan="4"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_main_label" colspan="4"><?php _e('Submit Button', 'ARMember'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_button_back_color" type="text" name="arm_form_settings[style][button_back_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['button_back_color']; ?>"/>
                                                                            <span><?php _e('Button Background', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <?php
                                                                        if (in_array($reference_template, array(3))) {
                                                                            ?>
                                                                            <td class="arm_custom_scheme_sub_label" id="arm_button_gradient_color" colspan="2">
                                                                                <input id="arm_button_back_color_gradient" type="text" name="arm_form_settings[style][button_back_color_gradient]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['button_back_color_gradient']; ?>"/>
                                                                                <span><?php _e('Button Background 2', 'ARMember') ?></span>
                                                                            </td>
                                                                        <?php } ?>
                                                                        <td class="arm_custom_scheme_sub_label arm_button_font_color_wrapper <?php echo (!empty($form_settings['style']['button_style']) && $form_settings['style']['button_style'] == 'border') ? 'hidden_section' : ''; ?>">
                                                                            <input id="arm_button_font_color" type="text" name="arm_form_settings[style][button_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['button_font_color']; ?>"/>
                                                                            <span><?php _e('Button Font', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <?php
                                                                        if (!in_array($reference_template, array(3))) {
                                                                            ?>
                                                                            <td class="arm_custom_scheme_sub_label">
                                                                                <input id="arm_button_hover_color" type="text" name="arm_form_settings[style][button_hover_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['button_hover_color']; ?>"/>
                                                                                <span><?php _e('Hover Background', 'ARMember'); ?></span>
                                                                            </td>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                        <?php
                                                                        if (!in_array($reference_template, array(3))) {
                                                                            ?>
                                                                            <td class="arm_custom_scheme_sub_label arm_button_hover_font_color_wrapper <?php echo (!empty($form_settings['style']['button_style']) && $form_settings['style']['button_style'] == 'reverse_border') ? 'hidden_section' : ''; ?>">
                                                                                <input id="arm_button_hover_font_color" type="text" name="arm_form_settings[style][button_hover_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['button_hover_font_color']; ?>"/>
                                                                                <span><?php _e('Hover Font', 'ARMember'); ?></span>
                                                                            </td>
                                                                        <?php } ?>
                                                                    </tr>
                                                                    <tr>
                                                                        <?php
                                                                        if (in_array($reference_template, array(3))) {
                                                                            ?>
                                                                            <td class="arm_custom_scheme_sub_label">
                                                                                <input id="arm_button_hover_color" type="text" name="arm_form_settings[style][button_hover_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['button_hover_color']; ?>"/>
                                                                                <span><?php _e('Hover Background', 'ARMember'); ?></span>
                                                                            </td>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                        <?php
                                                                        if (in_array($reference_template, array(3))) {
                                                                            ?>
                                                                            <td class="arm_custom_scheme_sub_label" id="arm_button_hover_gradient_color" colspan="2">
                                                                                <input id="arm_button_hover_color_gradient" type="text" name="arm_form_settings[style][button_hover_color_gradient]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['button_hover_color_gradient']; ?>" />
                                                                                <span><?php _e('Hover Background 2', 'ARMember'); ?></span>
                                                                            </td>
                                                                        <?php } ?>
                                                                        <?php
                                                                        if (in_array($reference_template, array(3))) {
                                                                            ?>
                                                                            <td class="arm_custom_scheme_sub_label arm_button_hover_font_color_wrapper <?php echo (!empty($form_settings['style']['button_style']) && $form_settings['style']['button_style'] == 'reverse_border') ? 'hidden_section' : ''; ?>">
                                                                                <input id="arm_button_hover_font_color" type="text" name="arm_form_settings[style][button_hover_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['button_hover_font_color']; ?>"/>
                                                                                <span><?php _e('Hover Font', 'ARMember'); ?></span>
                                                                            </td>
                                                                        <?php } ?>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_divider" colspan="4"></td>
                                                                    </tr>
                                                                    <tr class=" arm_custom_scheme_sub_label_no_writer <?php echo ($formLayout == 'writer') ? 'hidden_section' : ''; ?>">
                                                                        <td class="arm_custom_scheme_main_label" colspan="4"><?php _e('Prefix / Suffix Icon Color', 'ARMember'); ?></td>
                                                                    </tr>
                                                                    <tr class=" arm_custom_scheme_sub_label_no_writer <?php echo ($formLayout == 'writer') ? 'hidden_section' : ''; ?>">
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_prefix_suffix_color" type="text" name="arm_form_settings[style][prefix_suffix_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['prefix_suffix_color']; ?>"/>
                                                                            <span><?php _e('Icon Color', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_divider arm_custom_scheme_sub_label_no_writer <?php echo ($formLayout == 'writer') ? 'hidden_section' : ''; ?>" colspan="4"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="arm_custom_scheme_main_label" colspan="4"><?php _e('Validation Color', 'ARMember'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <?php
                                                                        $d_error_font_color = $form_settings['style']['error_font_color'];
                                                                        $d_error_field_bg_color = $form_settings['style']['error_field_bg_color'];
                                                                        if ($formLayout == 'writer' || (isset($form_settings['style']['validation_type']) && $form_settings['style']['validation_type'] == 'standard')) {
                                                                            $d_error_font_color = $form_settings['style']['error_field_bg_color'];
                                                                            $d_error_field_bg_color = $form_settings['style']['error_font_color'];
                                                                        }
                                                                        
                                                                        ?>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_error_font_color" type="text" name="arm_form_settings[style][error_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['error_font_color']; ?>" data-old_color="<?php echo $d_error_font_color; ?>"/>
                                                                            <span><?php _e('Validation Message Font', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td class="arm_custom_scheme_sub_label">
                                                                            <input id="arm_error_field_border_color" type="text" name="arm_form_settings[style][error_field_border_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['error_field_border_color']; ?>"/>
                                                                            <span><?php _e('Error Field Border', 'ARMember'); ?></span>
                                                                        </td>
                                                                        <td class="arm_custom_scheme_sub_label arm_custom_scheme_sub_label_no_writer <?php echo ($formLayout == 'writer') ? 'hidden_section' : ''; ?>">
                                                                            <input id="arm_error_field_bg_color" type="text" name="arm_form_settings[style][error_field_bg_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo $form_settings['style']['error_field_bg_color']; ?>" data-old_color="<?php echo $d_error_field_bg_color; ?>"/>
                                                                            <span><?php _e('Validation Message Background', 'ARMember'); ?></span>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>    
                                            <li>
                                                <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Input Field Options', 'ARMember'); ?>:<i></i></a>
                                                <div id="three" class="arm_accordion">
                                                    <table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
                                                        <tr>
                                                            <td><?php _e('Field Width', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type="text" id="arm_field_width" name="arm_form_settings[style][field_width]" class="arm_field_width arm_form_setting_input arm_width_140" value="<?php echo!empty($form_settings['style']['field_width']) ? $form_settings['style']['field_width'] : '100'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(%)&nbsp;</span>
                                                                    <input type='hidden' id="arm_field_width_type" name="arm_form_settings[style][field_width_type]" class="arm_field_width_type" value="%" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Field Height', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type="text" id="arm_field_height" name="arm_form_settings[style][field_height]" class="arm_field_height arm_form_setting_input arm_width_140" value="<?php echo isset($form_settings['style']['field_height']) ? $form_settings['style']['field_height'] : '33'; ?>"  onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Field Spacing', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type="text" id="arm_field_spacing" name="arm_form_settings[style][field_spacing]" class="arm_field_spacing arm_form_setting_input arm_width_140" value="<?php echo isset($form_settings['style']['field_spacing']) ? $form_settings['style']['field_spacing'] : '10'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="arm_vertical_align_top"><?php _e('Border', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='text' id="arm_field_border_width" name="arm_form_settings[style][field_border_width]" class="arm_field_border_width arm_form_setting_input arm_width_80" value="<?php echo isset($form_settings['style']['field_border_width']) ? $form_settings['style']['field_border_width'] : '1'; ?>" onkeydown="javascript:return checkNumber(event)" />
                                                                    <br />Width (px)
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='text' id="arm_field_border_radius" name="arm_form_settings[style][field_border_radius]" class="arm_field_border_radius arm_form_setting_input arm_width_80" value="<?php echo isset($form_settings['style']['field_border_radius']) ? $form_settings['style']['field_border_radius'] : '3'; ?>" onkeydown="javascript:return checkNumber(event)" />

                                                                    <br />Radius (px)
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_field_border_style" name="arm_form_settings[style][field_border_style]" class="arm_field_border_style" value="<?php echo!empty($form_settings['style']['field_border_style']) ? $form_settings['style']['field_border_style'] : 'solid'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_140">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_field_border_style">
                                                                                <li data-label="Solid" data-value="solid">Solid</li>
                                                                                <li data-label="Dashed" data-value="dashed">Dashed</li>
                                                                                <li data-label="Dotted" data-value="dotted">Dotted</li>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                    <br />Style
                                                                </div>
                                                            </td>                                            
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Field Alignment', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <?php $form_settings['style']['field_position'] = (!empty($form_settings['style']['field_position'])) ? $form_settings['style']['field_position'] : 'left'; ?>
                                                                    <div class="arm_switch arm_switch3 arm_field_position_switch">
                                                                        <label data-value="left" class="arm_switch_label <?php echo ($form_settings['style']['field_position'] == 'left') ? 'active' : ''; ?>"><?php _e('Left', 'ARMember'); ?></label>
                                                                        <label data-value="center" class="arm_switch_label <?php echo ($form_settings['style']['field_position'] == 'center') ? 'active' : ''; ?>"><?php _e('Center', 'ARMember'); ?></label>
                                                                        <label data-value="right" class="arm_switch_label <?php echo ($form_settings['style']['field_position'] == 'right') ? 'active' : ''; ?>"><?php _e('Right', 'ARMember'); ?></label>
                                                                        <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][field_position]" value="<?php echo $form_settings['style']['form_position']; ?>">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="font_settings_label"><?php _e('Font Settings', 'ARMember'); ?></td>
                                                            <td colspan="2"></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Family', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_field_font_family" name="arm_form_settings[style][field_font_family]" class="arm_field_font_family" value="<?php echo!empty($form_settings['style']['field_font_family']) ? $form_settings['style']['field_font_family'] : 'Helvetica'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_150">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_field_font_family">
                                                                                <?php echo $arm_member_forms->arm_fonts_list(); ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Size', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_field_font_size" name="arm_form_settings[style][field_font_size]" class="arm_field_font_size" value="<?php echo isset($form_settings['style']['field_font_size']) ? $form_settings['style']['field_font_size'] : '14'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_120">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_field_font_size">
                                                                                <?php
                                                                                for ($i = 8; $i < 41; $i++) {
                                                                                    ?><li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li><?php
                                                                                }
                                                                                ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                    <span>(px)</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Style', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <div class="arm_font_style_options">
                                                                        <!--/. Font Bold Option ./-->
                                                                        <label class="arm_font_style_label <?php echo ($form_settings['style']['field_font_bold'] == '1') ? 'arm_style_active' : ''; ?>" data-value="bold" data-field="arm_field_font_bold"><i class="armfa armfa-bold"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][field_font_bold]" id="arm_field_font_bold" class="arm_field_font_bold" value="<?php echo $form_settings['style']['field_font_bold']; ?>" />
                                                                        <!--/. Font Italic Option ./-->
                                                                        <label class="arm_font_style_label <?php echo ($form_settings['style']['field_font_italic'] == '1') ? 'arm_style_active' : ''; ?>" data-value="italic" data-field="arm_field_font_italic"><i class="armfa armfa-italic"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][field_font_italic]" id="arm_field_font_italic" class="arm_field_font_italic" value="<?php echo $form_settings['style']['field_font_italic']; ?>" />
                                                                        <!--/. Text Decoration Options ./-->
                                                                        <label class="arm_font_style_label arm_decoration_label <?php echo ($form_settings['style']['field_font_decoration'] == 'underline') ? 'arm_style_active' : ''; ?>" data-value="underline" data-field="arm_field_font_decoration"><i class="armfa armfa-underline"></i></label>
                                                                        <label class="arm_font_style_label arm_decoration_label <?php echo ($form_settings['style']['field_font_decoration'] == 'line-through') ? 'arm_style_active' : ''; ?>" data-value="line-through" data-field="arm_field_font_decoration"><i class="armfa armfa-strikethrough"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][field_font_decoration]" id="arm_field_font_decoration" class="arm_field_font_decoration" value="<?php echo $form_settings['style']['field_font_decoration']; ?>" />
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Text Direction', 'ARMember'); ?></td>
                                                            <td colspan="2">
                                                                <div class="arm_right">
                                                                    <div class="arm_switch arm_form_rtl_switch">
                                                                        <label data-value="0" class="arm_switch_label <?php echo ($is_rtl == '0') ? 'active' : ''; ?>"><?php _e('LTR', 'ARMember'); ?></label>
                                                                        <label data-value="1" class="arm_switch_label <?php echo ($is_rtl == '1') ? 'active' : ''; ?>"><?php _e('RTL', 'ARMember'); ?></label>
                                                                        <input type="hidden" class="arm_switch_radio arm_form_rtl_support_chk" name="arm_form_settings[style][rtl]" value="<?php echo $is_rtl; ?>">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php if ($isRegister || $isEditProfile) { ?>
                                                            <tr>
                                                                <td class="font_settings_label"><?php _e('Calendar Style', 'ARMember'); ?></td>
                                                                <td colspan="2"></td>
                                                            </tr>
                                                            <tr>
                                                                <td><?php _e('Date Format', 'ARMember'); ?></td>
                                                                <td colspan="2">
                                                                    <div class="arm_right">
                                                                        <?php

                                                                        $wp_default_dateFormatOpts = array('F d, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y');


                                                                        $dateFormatOpts = array('d/m/Y', 'm/d/Y', 'Y/m/d', 'M d, Y', 'F d, Y');
                                                                        $wp_format_date = get_option('date_format');
                                                                        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
                                                                            $dateFormatOpts = array('m/d/Y', 'M d, Y', 'F d, Y');
                                                                            
                                                                             $dateFormatOpts = array_unique(array_merge($dateFormatOpts, $wp_default_dateFormatOpts));



                                                                            if(!in_array($formDateFormat, $dateFormatOpts)){
                                                                                if (in_array($formDateFormat, array('m/d/Y', 'd/m/Y', 'Y/m/d'))) {
                                                                                    $formDateFormat = 'm/d/Y';
                                                                                } elseif (in_array($formDateFormat, array('M d, Y', 'd M, Y', 'Y, M d'))) {
                                                                                    $formDateFormat = 'M d, Y';
                                                                                } elseif (in_array($formDateFormat, array('F d, Y', 'd F, Y', 'Y, F d'))) {
                                                                                    $formDateFormat = 'F d, Y';
                                                                                }
                                                                            }
                                                                        } else if ($wp_format_date == 'd/m/Y') {
                                                                            $dateFormatOpts = array('d/m/Y', 'd M, Y', 'd F, Y');
                                                                            $dateFormatOpts = array_unique(array_merge($dateFormatOpts, $wp_default_dateFormatOpts));

                                                                            if(!in_array($formDateFormat, $dateFormatOpts)){
                                                                                if (in_array($formDateFormat, array('m/d/Y', 'd/m/Y', 'Y/m/d'))) {
                                                                                    $formDateFormat = 'd/m/Y';
                                                                                } elseif (in_array($formDateFormat, array('M d, Y', 'd M, Y', 'Y, M d'))) {
                                                                                    $formDateFormat = 'd M, Y';
                                                                                } elseif (in_array($formDateFormat, array('F d, Y', 'd F, Y', 'Y, F d'))) {
                                                                                    $formDateFormat = 'd F, Y';
                                                                                }
                                                                            }
                                                                        } else if ($wp_format_date == 'Y/m/d') {
                                                                            $dateFormatOpts = array('Y/m/d', 'Y, M d', 'Y, F d');

                                                                            $dateFormatOpts = array_unique(array_merge($dateFormatOpts, $wp_default_dateFormatOpts));

                                                                            if(!in_array($formDateFormat, $dateFormatOpts)){
                                                                                if (in_array($formDateFormat, array('m/d/Y', 'd/m/Y', 'Y/m/d'))) {
                                                                                    $formDateFormat = 'Y/m/d';
                                                                                } elseif (in_array($formDateFormat, array('M d, Y', 'd M, Y', 'Y, M d'))) {
                                                                                    $formDateFormat = 'Y, M d';
                                                                                } elseif (in_array($formDateFormat, array('F d, Y', 'd F, Y', 'Y, F d'))) {
                                                                                    $formDateFormat = 'Y, F d';
                                                                                }
                                                                            }
                                                                        } else {
                                                                            $dateFormatOpts = array('d/m/Y', 'm/d/Y', 'Y/m/d', 'M d, Y', 'F d, Y');
                                                                        }

                                                                        $dateFormatOpts = array_unique(array_merge($dateFormatOpts, $wp_default_dateFormatOpts));
                                                                        
                                                                        ?>
                                                                        <input type='hidden' id="arm_calendar_date_format" name="arm_form_settings[date_format]" class="arm_calendar_date_format armIgnore" value="<?php echo $formDateFormat; ?>" />
                                                                        <dl class="arm_selectbox column_level_dd arm_width_150">
                                                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                            <dd>
                                                                                <ul data-id="arm_calendar_date_format"><?php
                                                                                    foreach ($dateFormatOpts as $df) {
                                                                                        echo '<li data-label="' . date($df, current_time('timestamp')) . '" data-value="' . $df . '">' . date($df, current_time('timestamp')) . '</li>';
                                                                                    }
                                                                                    ?></ul>
                                                                            </dd>
                                                                        </dl>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><?php _e('Show Time', 'ARMember'); ?></td>
                                                                <td colspan="2">
                                                                    <div class="arm_right">
                                                                        <div class="arm_switch arm_show_time_switch">
                                                                            <label data-value="1" class="arm_switch_label <?php echo ($showTimePicker == '1') ? 'active' : ''; ?>"><?php _e('Yes', 'ARMember'); ?></label>
                                                                            <label data-value="0" class="arm_switch_label <?php echo ($showTimePicker == '0') ? 'active' : ''; ?>"><?php _e('No', 'ARMember'); ?></label>
                                                                            <input type="hidden" class="arm_switch_radio" name="arm_form_settings[show_time]" value="<?php echo $showTimePicker; ?>">
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </div>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Label Options', 'ARMember'); ?>:<i></i></a>
                                                <div id="four" class="arm_accordion">
                                                    <table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
                                                        <tr>
                                                            <td><?php _e('Label Width', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type="text" id="arm_label_width" name="arm_form_settings[style][label_width]" class="arm_label_width arm_form_setting_input arm_width_140" value="<?php echo!empty($form_settings['style']['label_width']) ? $form_settings['style']['label_width'] : '150'; ?>" onkeydown="javascript:return checkNumber(event)"/>&nbsp;(px)
                                                                    <input type='hidden' id="arm_label_width_type" name="arm_form_settings[style][label_width_type]" class="arm_label_width_type" value="px" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="arm_field_label_position_container">
                                                            <td><?php _e('Position', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <?php
                                                                    $form_settings['style']['label_position'] = (!empty($form_settings['style']['label_position'])) ? $form_settings['style']['label_position'] : 'inline';
                                                                    ?>
                                                                    <div class="arm_switch arm_switch3 arm_label_position_switch">
                                                                        <label data-value="block" class="arm_switch_label <?php echo ($form_settings['style']['label_position'] == 'block') ? 'active' : ''; ?>"><?php _e('Top', 'ARMember'); ?></label>
                                                                        <label data-value="inline" class="arm_switch_label <?php echo ($form_settings['style']['label_position'] == 'inline') ? 'active' : ''; ?>"><?php _e('Left', 'ARMember'); ?></label>
                                                                        <label data-value="inline_right" class="arm_switch_label <?php echo ($form_settings['style']['label_position'] == 'inline_right') ? 'active' : ''; ?>"><?php _e('Right', 'ARMember'); ?></label>
                                                                        <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][label_position]" value="<?php echo $form_settings['style']['label_position']; ?>">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Align', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <div class="arm_switch arm_label_align_switch">
                                                                        <label data-value="left" class="arm_switch_label <?php echo ($form_settings['style']['label_align'] == 'left') ? 'active' : ''; ?>"><?php _e('Left', 'ARMember'); ?></label>
                                                                        <label data-value="right" class="arm_switch_label <?php echo ($form_settings['style']['label_align'] == 'right') ? 'active' : ''; ?>"><?php _e('Right', 'ARMember'); ?></label>
                                                                        <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][label_align]" value="<?php echo $form_settings['style']['label_align']; ?>">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="arm_field_label_hide_container <?php echo ($formLayout == 'writer') ? 'hidden_section' : ''; ?>">
                                                            <td><?php _e('Hide Label', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <?php $form_settings['style']['label_hide'] = (!empty($form_settings['style']['label_hide'])) ? $form_settings['style']['label_hide'] : '0'; ?>
                                                                    <div class="arm_switch arm_label_hide_switch">
                                                                        <label data-value="1" class="arm_switch_label <?php echo ($form_settings['style']['label_hide'] == '1') ? 'active' : ''; ?>"><?php _e('Yes', 'ARMember'); ?></label>
                                                                        <label data-value="0" class="arm_switch_label <?php echo ($form_settings['style']['label_hide'] == '0') ? 'active' : ''; ?>"><?php _e('No', 'ARMember'); ?></label>
                                                                        <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][label_hide]" value="<?php echo $form_settings['style']['label_hide']; ?>">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="font_settings_label"><?php _e('Font Settings', 'ARMember'); ?></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Family', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_label_font_family" name="arm_form_settings[style][label_font_family]" class="arm_label_font_family" value="<?php echo!empty($form_settings['style']['label_font_family']) ? $form_settings['style']['label_font_family'] : 'Helvetica'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_150">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_label_font_family">
                                                                                <?php echo $arm_member_forms->arm_fonts_list(); ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Size', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_label_font_size" name="arm_form_settings[style][label_font_size]" class="arm_label_font_size" value="<?php echo!empty($form_settings['style']['label_font_size']) ? $form_settings['style']['label_font_size'] : '16'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_120">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_label_font_size">
                                                                                <?php
                                                                                for ($i = 8; $i < 41; $i++) {
                                                                                    ?><li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li><?php
                                                                                }
                                                                                ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                    <span>(px)</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Desc. Font Size', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_description_font_size" name="arm_form_settings[style][description_font_size]" class="arm_description_font_size" value="<?php echo!empty($form_settings['style']['description_font_size']) ? $form_settings['style']['description_font_size'] : '16'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_120">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_description_font_size">
                                                                                <?php
                                                                                for ($i = 8; $i < 41; $i++) {
                                                                                    ?><li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li><?php
                                                                                }
                                                                                ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                    <span>(px)</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Style', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <div class="arm_font_style_options">
                                                                        <!--/. Font Bold Option ./-->
                                                                        <label class="arm_font_style_label <?php echo ($form_settings['style']['label_font_bold'] == '1') ? 'arm_style_active' : ''; ?>" data-value="bold" data-field="arm_label_font_bold"><i class="armfa armfa-bold"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][label_font_bold]" id="arm_label_font_bold" class="arm_label_font_bold" value="<?php echo $form_settings['style']['label_font_bold']; ?>" />
                                                                        <!--/. Font Italic Option ./-->
                                                                        <label class="arm_font_style_label <?php echo ($form_settings['style']['label_font_italic'] == '1') ? 'arm_style_active' : ''; ?>" data-value="italic" data-field="arm_label_font_italic"><i class="armfa armfa-italic"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][label_font_italic]" id="arm_label_font_italic" class="arm_label_font_italic" value="<?php echo $form_settings['style']['label_font_italic']; ?>" />
                                                                        <!--/. Text Decoration Options ./-->
                                                                        <label class="arm_font_style_label arm_decoration_label <?php echo ($form_settings['style']['label_font_decoration'] == 'underline') ? 'arm_style_active' : ''; ?>" data-value="underline" data-field="arm_label_font_decoration"><i class="armfa armfa-underline"></i></label>
                                                                        <label class="arm_font_style_label arm_decoration_label <?php echo ($form_settings['style']['label_font_decoration'] == 'line-through') ? 'arm_style_active' : ''; ?>" data-value="line-through" data-field="arm_label_font_decoration"><i class="armfa armfa-strikethrough"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][label_font_decoration]" id="arm_label_font_decoration" class="arm_label_font_decoration" value="<?php echo $form_settings['style']['label_font_decoration']; ?>" />
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Submit Button Options', 'ARMember'); ?>:<i></i></a>
                                                <div id="five" class="arm_accordion">
                                                    <table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
                                                        <tr>
                                                            <td><?php _e('Width', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type="text" id="arm_button_width" name="arm_form_settings[style][button_width]" class="arm_button_width arm_form_setting_input arm_width_140" value="<?php echo!empty($form_settings['style']['button_width']) ? $form_settings['style']['button_width'] : '150'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
                                                                    <input type='hidden' id="arm_button_width_type" name="arm_form_settings[style][button_width_type]" class="arm_button_width_type" value="<?php echo!empty($form_settings['style']['button_width_type']) ? $form_settings['style']['button_width_type'] : 'px'; ?>" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Height', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type="text" id="arm_button_height" name="arm_form_settings[style][button_height]" class="arm_button_height arm_form_setting_input arm_width_140" value="<?php echo!empty($form_settings['style']['button_height']) ? $form_settings['style']['button_height'] : '35'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
                                                                    <input type='hidden' id="arm_button_height_type" name="arm_form_settings[style][button_height_type]" class="arm_button_height_type" value="<?php echo!empty($form_settings['style']['button_height_type']) ? $form_settings['style']['button_height_type'] : 'px'; ?>" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Border Radius', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type="text" id="arm_button_border_radius" name="arm_form_settings[style][button_border_radius]" class="arm_button_border_radius arm_form_setting_input arm_width_140" value="<?php echo isset($form_settings['style']['button_border_radius']) ? $form_settings['style']['button_border_radius'] : '4'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Button Style', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_button_style" name="arm_form_settings[style][button_style]" class="arm_button_style" value="<?php echo!empty($form_settings['style']['button_style']) ? $form_settings['style']['button_style'] : 'flat'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_150">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_button_style">
                                                                                <li data-value="flat" data-label="<?php _e('Flat', 'ARMember'); ?>"><?php _e('Flat', 'ARMember'); ?></li>
                                                                                <li data-value="classic" data-label="<?php _e('Classic', 'ARMember'); ?>"><?php _e('Classic', 'ARMember'); ?></li>
                                                                                <li data-value="border" data-label="<?php _e('Border', 'ARMember'); ?>"><?php _e('Border', 'ARMember'); ?></li>
                                                                                <li data-value="reverse_border" data-label="<?php _e('Reverse Border', 'ARMember'); ?>"><?php _e('Reverse Border', 'ARMember'); ?></li>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="font_settings_label"><?php _e('Font Settings', 'ARMember'); ?></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Family', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_button_font_family" name="arm_form_settings[style][button_font_family]" class="arm_button_font_family" value="<?php echo!empty($form_settings['style']['button_font_family']) ? $form_settings['style']['button_font_family'] : 'Helvetica'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_150">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_button_font_family">
                                                                                <?php echo $arm_member_forms->arm_fonts_list(); ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Size', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <input type='hidden' id="arm_button_font_size" name="arm_form_settings[style][button_font_size]" class="arm_button_font_size" value="<?php echo!empty($form_settings['style']['button_font_size']) ? $form_settings['style']['button_font_size'] : '16'; ?>" />
                                                                    <dl class="arm_selectbox column_level_dd arm_width_130">
                                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>
                                                                            <ul data-id="arm_button_font_size">
                                                                                <?php
                                                                                for ($i = 8; $i < 41; $i++) {
                                                                                    ?><li data-label="<?php echo $i; ?>" data-value="<?php echo $i; ?>"><?php echo $i; ?></li><?php
                                                                                }
                                                                                ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                    <span>px</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Font Style', 'ARMember'); ?></td>
                                                            <td>
                                                                <div class="arm_right">
                                                                    <div class="arm_font_style_options">
                                                                        <!--/. Font Bold Option ./-->
                                                                        <label class="arm_font_style_label <?php echo ($form_settings['style']['button_font_bold'] == '1') ? 'arm_style_active' : ''; ?>" data-value="bold" data-field="arm_button_font_bold"><i class="armfa armfa-bold"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][button_font_bold]" id="arm_button_font_bold" class="arm_button_font_bold" value="<?php echo $form_settings['style']['button_font_bold']; ?>" />
                                                                        <!--/. Font Italic Option ./-->
                                                                        <label class="arm_font_style_label <?php echo ($form_settings['style']['button_font_italic'] == '1') ? 'arm_style_active' : ''; ?>" data-value="italic" data-field="arm_button_font_italic"><i class="armfa armfa-italic"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][button_font_italic]" id="arm_button_font_italic" class="arm_button_font_italic" value="<?php echo $form_settings['style']['button_font_italic']; ?>" />
                                                                        <!--/. Text Decoration Options ./-->
                                                                        <label class="arm_font_style_label arm_decoration_label <?php echo ($form_settings['style']['button_font_decoration'] == 'underline') ? 'arm_style_active' : ''; ?>" data-value="underline" data-field="arm_button_font_decoration"><i class="armfa armfa-underline"></i></label>
                                                                        <label class="arm_font_style_label arm_decoration_label <?php echo ($form_settings['style']['button_font_decoration'] == 'line-through') ? 'arm_style_active' : ''; ?>" data-value="line-through" data-field="arm_button_font_decoration"><i class="armfa armfa-strikethrough"></i></label>
                                                                        <input type="hidden" name="arm_form_settings[style][button_font_decoration]" id="arm_button_font_decoration" class="arm_button_font_decoration" value="<?php echo $form_settings['style']['button_font_decoration']; ?>" />
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <?php _e('Margin', 'ARMember'); ?>
                                                            </td>
                                                            <td style="padding-right: 0;">
                                                                <?php
                                                                $form_settings['style']['button_margin_left'] = (is_numeric($form_settings['style']['button_margin_left'])) ? $form_settings['style']['button_margin_left'] : 0;
                                                                $form_settings['style']['button_margin_top'] = (is_numeric($form_settings['style']['button_margin_top'])) ? $form_settings['style']['button_margin_top'] : 0;
                                                                $form_settings['style']['button_margin_right'] = (is_numeric($form_settings['style']['button_margin_right'])) ? $form_settings['style']['button_margin_right'] : 0;
                                                                $form_settings['style']['button_margin_bottom'] = (is_numeric($form_settings['style']['button_margin_bottom'])) ? $form_settings['style']['button_margin_bottom'] : 0;
                                                                ?>
                                                                <div class="arm_button_margin_inputs_container">
                                                                    <div class="arm_button_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[style][button_margin_left]" id="arm_button_margin_left" class="arm_button_margin_left" value="<?php echo $form_settings['style']['button_margin_left']; ?>"/>
                                                                        <br /><?php _e('Left', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_button_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[style][button_margin_top]" id="arm_button_margin_top" class="arm_button_margin_top" value="<?php echo $form_settings['style']['button_margin_top']; ?>"/>
                                                                        <br /><?php _e('Top', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_button_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[style][button_margin_right]" id="arm_button_margin_right" class="arm_button_margin_right" value="<?php echo $form_settings['style']['button_margin_right']; ?>"/>
                                                                        <br /><?php _e('Right', 'ARMember'); ?>
                                                                    </div>
                                                                    <div class="arm_button_margin_inputs">
                                                                        <input type="text" name="arm_form_settings[style][button_margin_bottom]" id="arm_button_margin_bottom" class="arm_button_margin_bottom" value="<?php echo $form_settings['style']['button_margin_bottom']; ?>"/>
                                                                        <br /><?php _e('Bottom', 'ARMember'); ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php _e('Button Position', 'ARMember'); ?></td>															<td>
                                                                <div class="arm_right">
                                                                    <?php $form_settings['style']['button_position'] = (!empty($form_settings['style']['button_position'])) ? $form_settings['style']['button_position'] : 'left'; ?>
                                                                    <div class="arm_switch arm_switch3 arm_button_position_switch">
                                                                        <label data-value="left" class="arm_switch_label <?php echo ($form_settings['style']['button_position'] == 'left') ? 'active' : ''; ?>"><?php _e('Left', 'ARMember'); ?></label>
                                                                        <label data-value="center" class="arm_switch_label <?php echo ($form_settings['style']['button_position'] == 'center') ? 'active' : ''; ?>"><?php _e('Center', 'ARMember'); ?></label>
                                                                        <label data-value="right" class="arm_switch_label <?php echo ($form_settings['style']['button_position'] == 'right') ? 'active' : ''; ?>"><?php _e('Right', 'ARMember'); ?></label>
                                                                        <input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][button_position]" value="<?php echo $form_settings['style']['button_position']; ?>">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Custom Css', 'ARMember'); ?>:<i></i></a>
                                                <div id="six" class="arm_accordion">
                                                    <div class="arm_form_settings_style_block arm_form_custom_css_wrapper">
                                                        <textarea name="arm_form_settings[custom_css]" col="40" row="10"><?php echo isset($form_settings['custom_css']) ? stripslashes_deep($form_settings['custom_css']) : ''; ?></textarea>
                                                    </div>
                                                    <div class="arm_form_settings_custom_style_block">
                                                        <span class="arm_section_custom_css_eg">(e.g.)&nbsp;&nbsp; .arm_form_field_submit_button{color:#000000;}</span>
                                                        <span class="arm_section_custom_css_section" style="display: inline-block;">
                                                            <a class="arm_section_custom_css_detail arm_section_custom_css_detail_link" href="javascript:void(0)" data-section="arm_form" style="padding: 0px;"><?php _e('CSS Class Information', 'ARMember'); ?></a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="armclear"></div>
                                </div>
                            </div>
                        </div>
                    </div><!--./ END `.arm_editor_right_wrapper`-->
                </div><!--./ END `.arm_editor_right`-->
            </div>
        </form>
        <div class="armclear"></div>
    </div>
    <div class="arm_section_custom_css_detail_container"></div>
</div>
<div id="arm_fontawesome_modal" class="arm_manage_form_fa_icons_wrapper hidden_section">
    <div class="arm_manage_form_fa_icons_container arm_slider_box_container">
        <div class="arm_slider_box_arrow"></div>
        <div class="arm_slider_box_heading"><?php _e('Font Awesome Icons', 'ARMember'); ?></div>
        <div class="arm_slider_box_body">
            <?php
            if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_font_awesome.php')) {
                include( MEMBERSHIP_VIEWS_DIR . '/arm_font_awesome.php');
            }
            ?>
        </div>
    </div>
</div>
<?php
/**
 * Social Profile Fields Popup (Social Network List)
 */
$socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
$activeSPF = array('facebook', 'twitter', 'linkedin');
if (!empty($socialFieldsOptions)) {
    $activeSPF = isset($socialFieldsOptions['arm_form_field_option']['options']) ? $socialFieldsOptions['arm_form_field_option']['options'] : array();
}
$activeSPF = (!empty($activeSPF)) ? $activeSPF : array();
?>
<div class="popup_wrapper arm_social_profile_fields_popup_wrapper">
    <table cellspacing="0">
        <tr class="popup_wrapper_inner">	
            <td class="popup_close_btn arm_popup_close_btn arm_social_profile_fields_close_btn"></td>
            <td class="popup_header"><?php _e('Social Profile Fields', 'ARMember'); ?></td>
            <td class="popup_content_text">
                <div class="arm_social_profile_fields_list_wrapper">
                    <?php if (!empty($socialProfileFields)) { ?>
                        <?php foreach ($socialProfileFields as $spfKey => $spfLabel) { ?>
                            <div class="arm_social_profile_field_item">
                                <input type="checkbox" class="arm_icheckbox arm_spf_active_checkbox" value="<?php echo $spfKey; ?>" name="arm_social_fields[]" id="arm_spf_<?php echo $spfKey; ?>_status" <?php echo (in_array($spfKey, $activeSPF)) ? 'checked="checked"' : ''; ?>>
                                <label for="arm_spf_<?php echo $spfKey; ?>_status"><?php echo $spfLabel; ?></label>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </td>
            <td class="popup_content_btn popup_footer">
                <div class="popup_content_btn_wrapper">
                    <button class="arm_save_btn arm_add_edit_social_profile_fields" id="arm_add_edit_social_profile_fields" type="button"><?php _e('Add', 'ARMember'); ?></button>
                    <button class="arm_cancel_btn popup_close_btn arm_social_profile_fields_close_btn" type="button"><?php _e('Cancel', 'ARMember'); ?></button>
                </div>
            </td>
        </tr>
    </table>
</div>
<?php echo $form_styles; ?>
<?php
/* Angular JS */
$ARMember->enqueue_angular_script();
?>
<style type="text/css">#wpbody-content{padding:0;}html{background: #FFFFFF;}#adminmenuwrap{z-index: 9970;}</style>
<?php
$arm_form_css = $arm_member_forms->arm_ajax_generate_form_styles($form_id, $form_settings, array(), $reference_template);
if (isset($arm_form_css['arm_link']) && !empty($arm_form_css['arm_link'])) {
    echo $arm_form_css['arm_link'];
} else {
    echo '<link id="google-font-' . $form_id . '" rel="stylesheet" type="text/css" href="#" />';
}
/**
 * Add Social Network Popup
 */
if ((!$isRegister && !$isEditProfile)) {
    if ($arm_social_feature->isSocialLoginFeature) {
        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_form_editor_social_network_popup.php')) {
            include( MEMBERSHIP_VIEWS_DIR . '/arm_form_editor_social_network_popup.php');
        }
    }
}
?>
<style type="text/css" id="arm_form_runtime_style"><?php echo $arm_form_css['arm_css']; ?></style>
<style type="text/css" id="arm_button_hover_color_style"></style>
<style type="text/css" id="arm_field_focus_color_style"></style>
<style type="text/css" id="arm_field_bg_color_style"></style>
<style type="text/css" id="arm_date_picker_color_style"></style>
<script type="text/javascript">
    __ARM_TITLE = '<?php _e('Title','ARMember'); ?>';
    __ARM_METAKEY = '<?php _e('Meta Key','ARMember'); ?>';
    __ARM_VALUE = '<?php _e('Meta Value','ARMember'); ?>';
    function armColorSchemes() {
        var ColorSchemes = <?php echo json_encode($form_color_schemes); ?>;
        return ColorSchemes;
    }
    function armButtonGradientScheme() {
        var GradientScheme = <?php echo json_encode($formButtonSchemes); ?>;
        return GradientScheme;
    }
    jQuery(window).on("load", function () {
        setTimeout(function () {
            adjustEditor();
        }, 100);
        arm_disable_form_fields();
    });
    jQuery(window).resize(function () {
        adjustEditor();
    });
    jQuery(function ($) {
        adjustEditor();
        jQuery(document).on('click','.arm_slider_arrow_left', function (e) {
            var container = jQuery(this).attr('data-id');
            var arm_editor_left_width = jQuery('.arm_editor_left').width();
            if (isNaN(arm_editor_left_width) && arm_editor_left_width == undefined ) { arm_editor_left_width = 0; }
            jQuery('.arm_editor_center').css({'width': (jQuery('.arm_editor_wrapper').width() - arm_editor_left_width - jQuery('.arm_editor_right').width() - 50) + 'px'});
            jQuery('.' + container).toggle("slide");
            jQuery('.arm_slider_arrow_left').hide();
        });
        jQuery(document).on('click','.arm_slider_arrow_right', function (e) {
            var container = jQuery(this).attr('data-id');
            jQuery('.' + container).toggle("slide");
            jQuery('.arm_slider_arrow_left').show();
            var arm_editor_left_width = jQuery('.arm_editor_left').width();
            if (isNaN(arm_editor_left_width) && arm_editor_left_width == undefined ) { arm_editor_left_width = 0; }
            jQuery('.arm_editor_center').css({'width': (jQuery('.arm_editor_wrapper').width() - arm_editor_left_width - 50) + 'px'});
        });
<?php if (!empty($form_detail)) { ?>
            jQuery(document).on('click', '#arm_reset_member_form', function () {
                location.reload();
            });
            jQuery(document).on('click', '#arm_save_member_form', function () {
                var form_data = '';
                var form_action_val = jQuery('#arm_manage_form_settings_form input.form_action_option_type:checked').val();
                if (form_action_val == 'page') {
                    var form_action_page = jQuery('#arm_manage_form_settings_form .form_action_redirect_page').val();
                    if (form_action_page == '' || form_action_page == 0) {
                        armToast('<?php  echo addslashes(__('Redirection page is required.', 'ARMember')); ?>', 'error');
                        jQuery('#arm_manage_form_settings_form .form_action_redirect_page').css('border-color', 'red');
                        return false;
                    }
                } else if (form_action_val == 'url') {
                    var form_action_url = jQuery('#arm_manage_form_settings_form .form_action_redirect_url').val();

                    if (form_action_url == '') {
                        armToast('<?php echo addslashes(__('Redirection url is required.', 'ARMember')); ?>', 'error');
                        jQuery('#arm_manage_form_settings_form .form_action_redirect_url').css('border-color', 'red');
                        return false;
                    }
                    if (form_action_url.match(/^\s+|\s+$/g)) {
                        armToast('<?php echo addslashes(__('Please enter valid URL.', 'ARMember')); ?>', 'error');
                        return false;
                    }
                }
                else if (form_action_val == 'conditional_redirect') {
                    var form_action_url = jQuery('#arm_manage_form_settings_form .form_action_redirect_conditional_redirect').val();

                    if (form_action_url == '') {
                        armToast('<?php echo addslashes(__('Default Redirection url is required.', 'ARMember')); ?>', 'error');
                        jQuery('#arm_manage_form_settings_form .form_action_redirect_conditional_redirect').css('border-color', 'red');
                        return false;
                    }
                    if (form_action_url.match(/^\s+|\s+$/g)) {
                        armToast('<?php echo addslashes(__('Please enter valid URL.', 'ARMember')); ?>', 'error');
                        return false;
                    }
                }
                else if (form_action_val == 'referral') {
                    var form_action_url = jQuery('#arm_manage_form_settings_form .form_action_redirect_referral').val();

                    if (form_action_url == '') {
                        armToast('<?php echo addslashes(__('Default Redirection url is required.', 'ARMember')); ?>', 'error');
                        jQuery('#arm_manage_form_settings_form .form_action_redirect_referral').css('border-color', 'red');
                        return false;
                    }
                    if (form_action_url.match(/^\s+|\s+$/g)) {
                        armToast('<?php echo addslashes(__('Please enter valid URL.', 'ARMember')); ?>', 'error');
                        return false;
                    }
                }
                else if( jQuery("#arm_opt_ins_cl_mode").prop("checked") && jQuery("#arm_opt_ins_cl_form_field").val() == '' ) {
                    armToast('<?php echo addslashes(__('Please select form field in Conditional Subscription.', 'ARMember')); ?>', 'error');
                    return false;
                }

                jQuery('.arm_loading').fadeIn('slow');
                form_data = jQuery('#arm_manage_form_settings_form').serialize();
                var arm_action = jQuery("#arm_action").val();
                jQuery(this).attr('disabled', 'disabled');

                jQuery.ajax({
                    type: "POST",
                    url: __ARMAJAXURL,
                    dataType: 'json',
                    data: 'action=save_member_forms&' + form_data,
                    success: function (response)
                    {
                        if (response.message == 'success') {
                            armToast('<?php  echo addslashes(__('Form Settings Saved Successfully.', 'ARMember')); ?>', 'success');
                            if (arm_action == 'new_form' || arm_action == 'duplicate_form') {
                                if (window.history.pushState) {
                                    var pageurl = ArmRemoveVariableFromURL(document.URL, 'action');
                                    pageurl = ArmRemoveVariableFromURL(pageurl, 'form_id');
                                    pageurl = ArmRemoveVariableFromURL(pageurl, 'set_name');
                                    pageurl = ArmRemoveVariableFromURL(pageurl, 'arm_set_name');
                                    pageurl = ArmRemoveVariableFromURL(pageurl, 'form_meta_fields');
                                    pageurl = ArmRemoveVariableFromURL(pageurl, 'redirect_type');
                                    pageurl = ArmRemoveVariableFromURL(pageurl, 'pageid');
                                    pageurl = ArmRemoveVariableFromURL(pageurl, 'redirect_url');
                                    pageurl += '&action=edit_form&form_id=' + response.form_id;
                                    jQuery("#arm_action").val('edit_form');
                                    jQuery("#arm_form_id").val(response.form_id);
                                    window.history.pushState({path: pageurl}, '', pageurl);
                                }
                                if (response.form_type == 'registration') {
                                    jQuery('.arm_form_shortcode_container .arm_shortcode_text .armCopyText').html("[arm_form id=\"" + response.form_id + "\"]");
                                    jQuery('.arm_form_shortcode_container .arm_shortcode_text .arm_click_to_copy_text').attr('data-code', "[arm_form id=\"" + response.form_id + "\"]");
                                    jQuery('.arm_form_shortcode_container').show();
                                } else if( response.form_type == 'edit_profile' ){
                                    jQuery('.arm_form_shortcode_container .arm_shortcode_text .armCopyText').html("[arm_profile_detail id=\"" + response.form_id + "\"]");
                                    jQuery('.arm_form_shortcode_container .arm_shortcode_text .arm_click_to_copy_text').attr('data-code', "[arm_profile_detail id=\"" + response.form_id + "\"]");
                                    jQuery('.arm_form_shortcode_container').show();
                                } else {
                                    var pageurl = ArmRemoveVariableFromURL(document.URL, 'form_id');
                                    var response_ids = response.form_ids;
                                    var form_ids = response_ids.split(',');
                                    pageurl += '&form_id=' + form_ids[0];
                                    window.history.pushState({path: pageurl}, '', pageurl);
                                    jQuery("#arm_login_form_ids").val(response.form_ids);
                                    jQuery('#form_set_id').val(response.arm_form_set);
                                }
                            }
                        }
                        else if(response.type=='error')
                        {
                            armToast(response.msg, 'error');
                        }
                        jQuery('.arm_loading').fadeOut();
                        jQuery(this).removeAttr('disabled');
                        return false;
                    }
                });
                jQuery('.arm_loading').fadeOut();
                return false;
            });
<?php } ?>
<?php if ($isRegister || $isEditProfile) { ?>
            jQuery(document).on('click', '.arm_field_type_list li:not(.arm_disabled)', function () {
                var field_type = jQuery(this).find('.arm_new_field a').attr('id');
                var form_id = '<?php echo $form_id; ?>';
                var check_old = 0;

                if (field_type == 'roles' || field_type == 'avatar') {
                    check_old = jQuery('.arm_form_wrapper_container_' + form_id + ' .arm_form_field_container_' + field_type).length;
                }
                var excludeKeys = ['first_name', 'last_name', 'user_login', 'user_email', 'user_pass', 'avatar', 'roles'];
                if (jQuery.inArray(field_type, excludeKeys) !== -1) {
                    check_old = jQuery('.arm_form_wrapper_container_' + form_id + ' .arm_form_field_container_' + field_type).length;
                    if (check_old == 0) {
                        check_old = jQuery('.arm_form_wrapper_container_' + form_id + ' li[data-meta_key="' + field_type + '"]').length;
                    }
                }
                if(jQuery('.arm_form_wrapper_container_' + form_id + ' li[data-meta_key="user_pass"]').length && field_type == "password")
                {
                    armToast('<?php echo addslashes(__('You have already added password field in you form.', 'ARMember')); ?>', 'error');
                    return false;
                }
                if (check_old > 0) {
                    alert('<?php echo addslashes(__('Sorry, You can not add this field twice in form', 'ARMember')); ?>');
                } else {
                    var clone = jQuery(this).clone();
                    jQuery('.arm_main_sortable.arm_form_wrapper_container_' + form_id + '').append(clone);
                    var $target = clone;
                    armProcessFormFieldSorting(form_id, field_type, $target, '0');
                    jQuery(window.opera ? 'html' : 'html, body').animate({scrollTop: jQuery('.arm_form_wrapper_container_' + form_id + ' li:last').offset().top - 180}, 'slow');
                }
                return false;
            });
<?php } ?>
    });
    jQuery(document).on('change', '.form_action_option_type', function (e) {
        e.stopPropagation();
        var val = jQuery(this).val();
        jQuery('.arm_lable_shortcode_wrapper').addClass('hidden_section');
        jQuery('.arm_lable_shortcode_wrapper_' + val).removeClass('hidden_section');
    });
    jQuery(document).on('change', '.arm_form_email_tool_radio', function (e) {
        e.stopPropagation();
        var type = jQuery(this).attr('data-type');
        if (jQuery(this).is(':checked')) {
            jQuery(this).parents('.arm_etool_options_container').find('.arm_etool_list_container').removeClass('hidden_section');
        } else {
            jQuery(this).parents('.arm_etool_options_container').find('.arm_etool_list_container').addClass('hidden_section');
        }
    });
    jQuery(document).on('click', '.arm_color_scheme_nav_link', function (e) {
        e.stopPropagation();
        jQuery('a[href="#tabsetting-2"]').trigger('click');
        jQuery('a[href="#tabsetting-2"]').trigger('click');
        jQuery('#arm_form_settings_styles_container').animate({scrollTop: jQuery('#arm_color_scheme_container').position().top}, 0);
        jQuery('#arm_color_scheme_container').trigger('click');
    });
</script>
<?php
    //echo $ARMember->arm_get_need_help_html_content('member-forms-editor');
?>