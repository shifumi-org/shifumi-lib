<?php

global $ARMember, $arm_global_settings, $arm_social_feature, $wpdb, $arm_errors;

$globalSettings = $arm_global_settings->global_settings;

$register_page_id = isset($globalSettings['register_page_id']) ? $globalSettings['register_page_id'] : 0;
$forgot_password_page_id = isset($globalSettings['forgot_password_page_id']) ? $globalSettings['forgot_password_page_id'] : 0;
$reg_redirect_id = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
$login_redirect_id = isset($globalSettings['edit_profile_page_id']) ? $globalSettings['edit_profile_page_id'] : 0;

$wp_upload_dir = wp_upload_dir();
$upload_dir = $wp_upload_dir['basedir'] . '/armember/';

/* Registration Template */

$forms = array();
$forms['arm_form_label'] = __('Template 1','ARMember');
$forms['arm_form_title'] = __('Please Signup','ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-registration';
$forms['arm_set_name'] = __('Template 1','ARMember');
$forms['arm_is_default'] = 1;
$forms['arm_is_template'] = 1;
$forms['arm_ref_template'] = 1;
$forms['arm_set_id'] = 0;
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_settings = array(
    'redirect_type' => 'page',
    'redirect_page' => $reg_redirect_id,
    'redirect_url' => '',
    'auto_login' => '1',
    'style' => array(
        'form_layout' => 'writer',
        'form_width' => '600',
        'form_width_type' => 'px',
        'form_border_width' => '0',
        'form_border_radius' => '8',
        'form_border_style' => 'solid',
        'form_padding_left' => '30',
        'form_padding_top' => '30',
        'form_padding_bottom' => '30',
        'form_padding_right' => '30',
        'form_position' => 'left',
        'form_bg' => '',
        'form_title_font_family' => 'Helvetica',
        'form_title_font_size' => '28',
        'form_title_font_bold' => '1',
        'form_title_font_italic' => '0',
        'form_title_font_decoration' => '',
        'form_title_position' => 'center',
        'validation_position' => 'bottom',
        'color_scheme' => 'blue',    
        'lable_font_color' => '#1A2538',
        'field_font_color' => '#2F3F5C',
        'field_border_color' => '#D3DEF0',
        'field_focus_color' => '#637799',
        'button_back_color' => '#005AEE',
        'button_font_color' => '#FFFFFF',
        'button_hover_color' => '#0D54C9',
        'button_hover_font_color' => '#ffffff',                                                                           
        'form_title_font_color' => '#1A2538',
        'form_bg_color' => "#FFFFFF",
        'form_border_color' => "#DCE5F2",
        'prefix_suffix_color' => '#bababa',
        'error_font_color' => '#FF3B3B',
        'error_field_border_color' => '#FF3B3B',
        'error_field_bg_color' => '#ffffff',   
        'field_width' => '100',
        'field_width_type' => '%',
        'field_spacing' => '15',
        'field_border_width' => '1',
        'field_border_radius' => '0',
        'field_border_style' => 'solid',
        'field_font_family' => 'Helvetica',
        'field_font_size' => '14',
        'field_font_bold' => '0',
        'field_font_italic' => '0',
        'field_font_decoration' => '',
        'field_position' => 'left',
        'rtl' => '0',
        'label_width' => '250',
        'label_width_type' => 'px',
        'label_position' => 'block',
        'label_align' => 'left',
        'label_hide' => '0',
        'label_font_family' => 'Helvetica',
        'label_font_size' => '16',
        'description_font_size' => '16',
        'label_font_bold' => '0',
        'label_font_italic' => '0',
        'label_font_decoration' => '',
        'button_width' => '350',
        'button_width_type' => 'px',
        'button_height' => '45',
        'button_height_type' => 'px',
        'button_border_radius' => '50',
        'button_style' => 'border',
        'button_font_family' => 'Helvetica',
        'button_font_size' => '18',
        'button_font_bold' => '1',
        'button_font_italic' => '0',
        'button_font_decoration' => '',
        'button_margin_left' => '0',
        'button_margin_top' => '10',
        'button_margin_right' => '0',
        'button_margin_bottom' => '0',
        'button_position' => 'center'
    )
);

$forms['arm_form_settings'] = maybe_serialize($form_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'label' => __('Username','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'user_login',
    'required' => '1',
    'hide_username' => '0',
    'blank_message' => __('Username can not be left blank','ARMember'),
    'invalid_message' => __('Please enter valid username','ARMember'),
    'default_field' => '1',
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'first_name',
    'label' => __('First Name','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'first_name',
    'required' => '1',
    'hide_firstname' => '0',
    'blank_message' => __('First Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'first_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'last_name',
    'label' => __('Last Name','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'last_name',
    'required' => '1',
    'hide_lastname' => '0',
    'blank_message' => __('Last Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'last_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_email',
    'label' => __('Email Address','ARMember'),
    'placeholder' => '',
    'type' => 'email',
    'meta_key' => 'user_email',
    'required' => '1',
    'blank_message' => __('Email Address can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid email address.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => 'user_email',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_pass',
    'label' => __("Password",'ARMember'),
    'placeholder' => '',
    'type' => 'password',
    'options' => array(
        'strength_meter' => '1',
        'strong_password' => '0',
        'minlength' => '6',
        'maxlength' => '',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid password.','ARMember')
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 5,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'submit',
    'label' => __('Submit','ARMember'),
    'type' => 'submit',
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 6,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);
unset($form_id);
unset($forms);

/* Registration Template */


/* Login Form Template Start */
$forms = array();
$forms['arm_form_label'] = __('Please Login', 'ARMember');
$forms['arm_form_title'] = __('Please Login','ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-login';
$forms['arm_set_name'] = __('Template 1', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 1;
$forms['arm_set_id'] = '-1';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings = array();
$form_settings = array();
$form_settings['display_direction'] = 'vertical';
$form_settings['redirect_type'] = 'page';
$form_settings['redirect_page'] = $login_redirect_id;
$form_settings['redirect_url'] = '';
$form_settings['show_rememberme'] = '1';
$form_settings['show_registration_link'] = '1';
$form_settings['registration_link_label'] = '<center>Dont have account? [ARMLINK]SIGNUP[/ARMLINK]</center>';
$form_settings['registration_link_type'] = 'page';
$form_settings['registration_link_type_modal'] = '1';
$form_settings['registration_link_type_page'] = $register_page_id;
$form_settings['show_forgot_password_link'] = '1';
$form_settings['forgot_password_link_label'] = 'Lost Your Password';
$form_settings['forgot_password_link_type'] = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top'] = '-132';
$form_settings['forgot_password_link_margin']['left'] = '315';
$form_settings['forgot_password_link_margin']['right'] = '0';
$form_settings['registration_link_margin']['top'] = 0;
$form_settings['registration_link_margin']['bottom'] = 0;
$form_settings['registration_link_margin']['left'] = 0;
$form_settings['registration_link_margin']['right'] = 0;

if ($arm_social_feature->isSocialFeature && !empty($arm_social_feature->isSocialFeature)) {
    $social_networks = $arm_social_feature->social_settings['options'];
    $forms_networks = array('facebook','twitter');
    $networks = '';
    $counter = 0;
    $network_order = '';
    foreach ($social_networks as $key => $network) {
        if (in_array($key, $forms_networks) && $network['status'] == '1') {
            $networks .= $key . ',';
            $counter++;
        }
        $network_order .= $key . ',';
    }
    if ($counter > 0) {
        $networks = rtrim($networks, ',');
        $network_order = rtrim($network_order, ',');
        $form_settings['enable_social_login'] = '1';
        $form_settings['social_networks'] = $networks;
        $form_settings['social_networks_order'] = $network_order;
        $form_settings['social_network_settings'] = $social_networks;
    }
}

$form_style = array(
    'social_btn_position' => 'bottom',
    'social_btn_type' => 'horizontal',
    'social_btn_align' => 'center',
    'enable_social_btn_separator' => '1',
    'social_btn_separator' => '<center>OR</center>',
    'form_layout' => 'writer',
    'form_width' => '550',
    'form_width_type' => 'px',
    'form_border_width' => '0',
    'form_border_radius' => '8',
    'form_border_style' => 'solid',
    'form_padding_left' => '30',
    'form_padding_top' => '30',
    'form_padding_right' => '30',
    'form_padding_bottom' => '30',
    'form_position' => 'left',
    'form_bg' => '',
    'form_title_font_family' => 'Helvetica',
    'form_title_font_size' => '28',
    'form_title_font_bold' => '1',
    'form_title_font_italic' => '0',
    'form_title_font_decoration' => '',
    'form_title_position' => 'center',
    'validation_position' => 'bottom',
    'color_scheme' => 'blue',    
    'lable_font_color' => '#1A2538',
    'field_font_color' => '#2F3F5C',
    'field_border_color' => '#D3DEF0',
    'field_focus_color' => '#637799',
    'button_back_color' => '#005AEE',
    'button_font_color' => '#FFFFFF',
    'button_hover_color' => '#0D54C9',
    'button_hover_font_color' => '#ffffff',                                                           
    'form_title_font_color' => '#1A2538',
    'form_bg_color' => "#FFFFFF",
    'form_border_color' => "#DCE5F2",
    'prefix_suffix_color' => '#bababa',
    'error_font_color' => '#FF3B3B',
    'error_field_border_color' => '#FF3B3B',
    'error_field_bg_color' => '#ffffff',   
    'field_width' => '100',
    'field_width_type' => '%',
    'field_spacing' => '15',
    'field_border_width' => '1',
    'field_border_radius' => '0',
    'field_border_style' => 'solid',
    'field_font_family' => 'Helvetica',
    'field_font_size' => '14',
    'field_font_bold' => '0',
    'field_font_italic' => '0',
    'field_font_decoration' => '',
    'field_position' => 'left',
    'rtl' => '0',
    'label_width' => '250',
    'label_width_type' => 'px',
    'label_position' => 'block',
    'label_align' => 'left',
    'label_hide' => '0',
    'label_font_family' => 'Helvetica',
    'label_font_size' => '16',
    'description_font_size' => '16',
    'label_font_bold' => '0',
    'label_font_italic' => '0',
    'label_font_decoration' => '',
    'button_width' => '350',
    'button_width_type' => 'px',
    'button_height' => '45',
    'button_height_type' => 'px',
    'button_border_radius' => '50',
    'button_style' => 'border',
    'button_font_family' => 'Helvetica',
    'button_font_size' => '18',
    'button_font_bold' => '1',
    'button_font_italic' => '0',
    'button_font_decoration' => '',
    'button_margin_left' => '0',
    'button_margin_top' => '10',
    'button_margin_right' => '0',
    'button_margin_bottom' => '0',
    'button_position' => 'center'
);

$form_settings['style'] = $form_style;

$form_template_settings = $form_settings;
$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;
$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'meta_key' => 'user_login',
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'user_pass',
    'label' => __('Password', 'ARMember'),
    'placeholder' => '',
    'type' => 'password',
    'default_field' => '1',
    'options' => array(
        'strength_meter' => '0',
        'strong_password' => '0',
        'minlength' => '1',
        'maxlength' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid password', 'ARMember')
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'rememberme',
    'type' => 'rememberme',
    'default_field' => '1',
    'default_val' => 'forever',
    'label' => __('Remember me', 'ARMember'),
    'meta_key' => 'rememberme',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'rememberme',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'LOGIN',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Login Form Template End */

/* Forgot Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Forgot Password', 'ARMember');
$forms['arm_form_title'] = __('Forgot Password','ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-forgot-password';
$forms['arm_set_name'] = __('Template 1', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 1;
$forms['arm_set_id'] = '-1';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = __('Please enter your email address or username below.','ARMember');

$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'meta_key' => 'user_login',
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Forgot Password Form End */

/* Change Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Change Password', 'ARMember');
$forms['arm_form_title'] = __('Change Password','ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-change-password';
$forms['arm_set_name'] = __('Template 1', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 1;
$forms['arm_set_id'] = '-1';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message'] = __('Your password changed successfully.','ARMember');


$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_pass',
    'type' => 'password',
    'default_field' => '1',
    'label' => __('New Password', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '6',
        'maxlength' => '',
        'strength_meter' => '1',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'required' => '1',
    'meta_key' => 'user_pass',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'repeat_pass',
    'type' => 'repeat_pass',
    'default_field' => '1',
    'label' => __('Confirm Password', 'ARMember'),
    'required' => '1',
    'meta_key' => 'repeat_pass',
    'blank_message' => __('Confirm Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Passwords don\'t match.','ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => $form_field_id
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'repeat_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);
$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);
unset($form_field_id);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);
unset($form_template_settings);

/* Change Password Form End */

/* First Set End */

/* Second Set Start */

$forms = array();
$forms['arm_form_label'] = __('Template 2','ARMember')."<hr style='border:2px solid #23b7e5;'/>";
$forms['arm_form_title'] = __('Please Signup','ARMember')."<hr style='border:2px solid #23b7e5;'/>";
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-registration-2';
$forms['arm_set_name'] = __('Template 2','ARMember');
$forms['arm_is_default'] = 1;
$forms['arm_is_template'] = 1;
$forms['arm_ref_template'] = 2;
$forms['arm_set_id'] = 0;
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_settings = array(
    'redirect_type' => 'page',
    'redirect_page' => $reg_redirect_id,
    'redirect_url' => '',
    'auto_login' => '1',
    'style' => array(
        'form_layout' => 'writer',
        'form_width' => '600',
        'form_width_type' => 'px',
        'form_border_width' => '0',
        'form_border_radius' => '10',
        'form_border_style' => 'solid',
        'form_padding_left' => '40',
        'form_padding_top' => '30',
        'form_padding_bottom' => '20',
        'form_padding_right' => '40',
        'form_position' => 'left',
        'form_bg' => '',
        'form_title_font_family' => 'Helvetica',
        'form_title_font_size' => '26',
        'form_title_font_bold' => '1',
        'form_title_font_italic' => '0',
        'form_title_font_decoration' => '',
        'form_title_position' => 'center',
        'validation_position' => 'bottom',
        'color_scheme' => 'blue',    
        'lable_font_color' => '#1A2538',
        'field_font_color' => '#2F3F5C',
        'field_border_color' => '#D3DEF0',
        'field_focus_color' => '#637799',
        'button_back_color' => '#005AEE',
        'button_font_color' => '#FFFFFF',
        'button_hover_color' => '#0D54C9',
        'button_hover_font_color' => '#ffffff',                                                                           
        'login_link_font_color' => '#005AEE',
        'register_link_font_color' => '#005AEE',
        'form_title_font_color' => '#1A2538',
        'form_bg_color' => "#FFFFFF",
        'form_border_color' => "#DCE5F2",
        'prefix_suffix_color' => '#bababa',
        'error_font_color' => '#FF3B3B',
        'error_field_border_color' => '#FF3B3B',
        'error_field_bg_color' => '#ffffff',   
        'field_width' => '110',
        'field_width_type' => '%',
        'field_height' => '35',
        'field_spacing' => '18',
        'field_border_width' => '1',
        'field_border_radius' => '0',
        'field_border_style' => 'solid',
        'field_font_family' => 'Helvetica',
        'field_font_size' => '14',
        'field_font_bold' => '0',
        'field_font_italic' => '0',
        'field_font_decoration' => '',
        'field_position' => 'left',
        'rtl' => '0',
        'label_width' => '250',
        'label_width_type' => 'px',
        'label_position' => 'block',
        'label_align' => 'left',
        'label_hide' => '0',
        'label_font_family' => 'Helvetica',
        'label_font_size' => '16',
        'description_font_size' => '16',
        'label_font_bold' => '0',
        'label_font_italic' => '0',
        'label_font_decoration' => '',
        'button_width' => '110',
        'button_width_type' => 'px',
        'button_height' => '110',
        'button_height_type' => 'px',
        'button_border_radius' => '90',
        'button_style' => 'border',
        'button_font_family' => 'Helvetica',
        'button_font_size' => '18',
        'button_font_bold' => '1',
        'button_font_italic' => '0',
        'button_font_decoration' => '',
        'button_margin_left' => '0',
        'button_margin_top' => '5',
        'button_margin_right' => '0',
        'button_margin_bottom' => '0',
        'button_position' => 'center'
    ),
    'custom_css' => '.arm_form_heading_container{padding-bottom:40px !important;}.arm_forgot_password_description{margin-top: -30px !important;margin-bottom: 40px !important;margin-left: 20px !important;}'
);

$forms['arm_form_settings'] = maybe_serialize($form_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'label' => __('Username','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'user_login',
    'required' => '1',
    'hide_username' => '0',
    'blank_message' => __('Username can not be left blank','ARMember'),
    'invalid_message' => __('Please enter valid username','ARMember'),
    'default_field' => '1',
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'first_name',
    'label' => __('First Name','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'first_name',
    'required' => '1',
    'hide_firstname' => '0',
    'blank_message' => __('First Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'first_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'last_name',
    'label' => __('Last Name','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'last_name',
    'required' => '1',
    'hide_lastname' => '0',
    'blank_message' => __('Last Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'last_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_email',
    'label' => __('Email Address','ARMember'),
    'placeholder' => '',
    'type' => 'email',
    'meta_key' => 'user_email',
    'required' => '1',
    'blank_message' => __('Email Address can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid email address.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => 'user_email',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_pass',
    'label' => __("Password",'ARMember'),
    'placeholder' => '',
    'type' => 'password',
    'options' => array(
        'strength_meter' => '1',
        'strong_password' => '0',
        'minlength' => '6',
        'maxlength' => '',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid password.','ARMember')
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 5,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'submit',
    'label' => __('Submit','ARMember'),
    'type' => 'submit',
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 6,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);
unset($form_id);
unset($forms);

/* Registration Template */

$forms = array();
$forms['arm_form_label'] = __('Please Login', 'ARMember')."<hr style='border:2px solid #23b7e5;' />";
$forms['arm_form_title'] = __('Please Login', 'ARMember')."<hr style='border:2px solid #23b7e5;' />";
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-login-2';
$forms['arm_set_name'] = __('Template 2', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 2;
$forms['arm_set_id'] = '-2';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings = array();
$form_settings = array();
$form_settings['display_direction'] = 'vertical';
$form_settings['redirect_type'] = 'page';
$form_settings['redirect_page'] = $login_redirect_id;
$form_settings['redirect_url'] = '';
$form_settings['show_rememberme'] = '1';
$form_settings['show_registration_link'] = '1';
$form_settings['registration_link_label'] = 'Dont have account? [ARMLINK]SIGNUP[/ARMLINK]';
$form_settings['registration_link_type'] = 'page';
$form_settings['registration_link_type_modal'] = '1';
$form_settings['registration_link_type_page'] = $register_page_id;
$form_settings['show_forgot_password_link'] = '1';
$form_settings['forgot_password_link_label'] = 'Forgot Password';
$form_settings['forgot_password_link_type'] = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top'] = '-198';
$form_settings['forgot_password_link_margin']['left'] = '320';
$form_settings['forgot_password_link_margin']['right'] = '0';
$form_settings['registration_link_margin']['top'] = '5';
$form_settings['registration_link_margin']['bottom'] = '0';
$form_settings['registration_link_margin']['left'] = '110';
$form_settings['registration_link_margin']['right'] = '0';

$form_style = array(
    'form_layout' => 'writer',
    'form_width' => '550',
    'form_width_type' => 'px',
    'form_border_width' => '0',
    'form_border_radius' => '10',
    'form_border_style' => 'solid',
    'form_padding_left' => '40',
    'form_padding_top' => '30',
    'form_padding_right' => '40',
    'form_padding_bottom' => '20',
    'form_position' => 'left',
    'form_bg' => '',
    'form_title_font_family' => 'Helvetica',
    'form_title_font_size' => '26',
    'form_title_font_bold' => '1',
    'form_title_font_italic' => '0',
    'form_title_font_decoration' => '',
    'form_title_position' => 'center',
    'validation_position' => 'bottom',
    'color_scheme' => 'blue',    
    'lable_font_color' => '#1A2538',
    'field_font_color' => '#2F3F5C',
    'field_border_color' => '#D3DEF0',
    'field_focus_color' => '#637799',
    'button_back_color' => '#005AEE',
    'button_font_color' => '#FFFFFF',
    'button_hover_color' => '#0D54C9',
    'button_hover_font_color' => '#ffffff',                                                                           
    'login_link_font_color' => '#005AEE',
    'register_link_font_color' => '#005AEE',
    'form_title_font_color' => '#1A2538',
    'form_bg_color' => "#FFFFFF",
    'form_border_color' => "#DCE5F2",
    'prefix_suffix_color' => '#bababa',
    'error_font_color' => '#FF3B3B',
    'error_field_border_color' => '#FF3B3B',
    'error_field_bg_color' => '#ffffff',   
    'field_width' => '100',
    'field_width_type' => '%',
    'field_spacing' => '18',
    'field_border_width' => '1',
    'field_border_radius' => '0',
    'field_border_style' => 'solid',
    'field_font_family' => 'Helvetica',
    'field_font_size' => '14',
    'field_font_bold' => '0',
    'field_font_italic' => '0',
    'field_font_decoration' => '',
    'field_position' => 'left',
    'rtl' => '0',
    'label_width' => '250',
    'label_width_type' => 'px',
    'label_position' => 'block',
    'label_align' => 'left',
    'label_hide' => '0',
    'label_font_family' => 'Helvetica',
    'label_font_size' => '16',
    'description_font_size' => '16',
    'label_font_bold' => '0',
    'label_font_italic' => '0',
    'label_font_decoration' => '',
    'button_width' => '110',
    'button_width_type' => 'px',
    'button_height' => '110',
    'button_height_type' => 'px',
    'button_border_radius' => '90',
    'button_style' => 'border',
    'button_font_family' => 'Helvetica',
    'button_font_size' => '18',
    'button_font_bold' => '1',
    'button_font_italic' => '0',
    'button_font_decoration' => '',
    'button_margin_left' => '0',
    'button_margin_top' => '5',
    'button_margin_right' => '0',
    'button_margin_bottom' => '0',
    'button_position' => 'center'
);

$form_settings['style'] = $form_style;

$form_settings['custom_css'] = ".arm_form_heading_container{padding-bottom:40px !important;}.arm_forgot_password_description{margin-top: -30px !important;margin-bottom: 40px !important;margin-left: 20px !important;}";

$form_template_settings = $form_settings;
$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;
$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'hide_username' => 0,
    'meta_key' => 'user_login',
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'user_pass',
    'label' => __('Password', 'ARMember'),
    'placeholder' => '',
    'type' => 'password',
    'default_field' => '1',
    'options' => array(
        'strength_meter' => '0',
        'strong_password' => '0',
        'minlength' => '1',
        'maxlength' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid password', 'ARMember')
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'rememberme',
    'type' => 'rememberme',
    'default_field' => '1',
    'label' => __('Remember me', 'ARMember'),
    'meta_key' => 'rememberme',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'rememberme',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'LOGIN',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Login Form Template End */

/* Forgot Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Forgot Password', 'ARMember')."<hr style='border:2px solid #23b7e5;' />";
$forms['arm_form_title'] = __('Forgot Password', 'ARMember')."<hr style='border:2px solid #23b7e5;' />";
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-forgot-password-2';
$forms['arm_set_name'] = __('Template 2', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 2;
$forms['arm_set_id'] = '-2';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = __('Please enter your email address or username below.','ARMember');

$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'meta_key' => 'user_login',
    'hide_username' => 0,
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Forgot Password Form End */

/* Change Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Change Password', 'ARMember')."<hr style='border:2px solid #23b7e5;' />";
$forms['arm_form_title'] = __('Change Password', 'ARMember')."<hr style='border:2px solid #23b7e5;' />";
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-change-password-2';
$forms['arm_set_name'] = __('Template 2', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 2;
$forms['arm_set_id'] = '-2';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message'] = __('Your password changed successfully.','ARMember');


$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_pass',
    'type' => 'password',
    'default_field' => '1',
    'label' => __('New Password', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '6',
        'maxlength' => '',
        'strength_meter' => '1',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'required' => '1',
    'meta_key' => 'user_pass',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'repeat_pass',
    'type' => 'repeat_pass',
    'default_field' => '1',
    'label' => __('Confirm Password', 'ARMember'),
    'required' => '1',
    'meta_key' => 'repeat_pass',
    'blank_message' => __('Confirm Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Passwords don\'t match.','ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => $form_field_id
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'repeat_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);
$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);
unset($form_field_id);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);
unset($form_template_settings);

/* Second Set End */

/* Third Set Start */


/* Registration Template */

$forms = array();
$forms['arm_form_label'] = __('Template 3','ARMember');
$forms['arm_form_title'] = __('Please Signup', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-registration-3';
$forms['arm_set_name'] = __('Template 3','ARMember');
$forms['arm_is_default'] = 1;
$forms['arm_is_template'] = 1;
$forms['arm_ref_template'] = 3;
$forms['arm_set_id'] = 0;
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_settings = array(
    'redirect_type' => 'page',
    'redirect_page' => $reg_redirect_id,
    'redirect_url' => '',
    'auto_login' => '1',
    'style' => array(
        'form_layout' => 'rounded',
        'form_width' => '600',
        'form_width_type' => 'px',
        'form_border_width' => '0',
        'form_border_radius' => '25',
        'form_border_style' => 'solid',
        'form_padding_left' => '45',
        'form_padding_top' => '40',
        'form_padding_bottom' => '45',
        'form_padding_right' => '30',
        'form_position' => 'left',
        'form_bg' => '',
        'form_title_font_family' => 'Helvetica',
        'form_title_font_size' => '26',
        'form_title_font_bold' => '1',
        'form_title_font_italic' => '0',
        'form_title_font_decoration' => '',
        'form_title_position' => 'center',
        'validation_position' => 'bottom',
        'color_scheme' => 'red',
        'lable_font_color' => '#919191',
        'field_font_color' => '#242424',
        'field_border_color' => '#dbdbdb',
        'field_focus_color' => '#b8b8b8',
        'button_back_color' => '#dd2476',
        'button_back_color_gradient' => '#ff512f',
        'button_font_color' => '#ffffff',
        'button_hover_color' => '#dd2476',
        'button_hover_font_color' => '#ffffff',
        'button_hover_color_gradient' => '#ff512f',
        "login_link_font_color" => '#e65e80',
        "register_link_font_color" => '#e65e80',
        'form_title_font_color' => '#bdbdbd',
        'form_bg_color' => '#ffffff',
        'form_border_color' => '#cccccc',
        'prefix_suffix_color' => '#bababa',
        'error_font_color' => '#ffffff',
        'error_field_border_color' => '#f05050',
        'error_field_bg_color' => '#e6594d',
        'field_width' => '100',
        'field_width_type' => '%',
        'field_height' => '45',
        'field_spacing' => '5',
        'field_border_width' => '2',
        'field_border_radius' => '40',
        'field_border_style' => 'solid',
        'field_font_family' => 'Helvetica',
        'field_font_size' => '14',
        'field_font_bold' => '0',
        'field_font_italic' => '0',
        'field_font_decoration' => '',
        'field_position' => 'left',
        'rtl' => '0',
        'label_width' => '250',
        'label_width_type' => 'px',
        'label_position' => 'block',
        'label_align' => 'left',
        'label_hide' => '1',
        'label_font_family' => 'Helvetica',
        'label_font_size' => '16',
        'description_font_size' => '16',
        'label_font_bold' => '0',
        'label_font_italic' => '0',
        'label_font_decoration' => '',
        'button_width' => '150',
        'button_width_type' => 'px',
        'button_height' => '50',
        'button_height_type' => 'px',
        'button_border_radius' => '50',
        'button_style' => 'flat',
        'button_font_family' => 'Helvetica',
        'button_font_size' => '18',
        'button_font_bold' => '1',
        'button_font_italic' => '0',
        'button_font_decoration' => '',
        'button_margin_left' => '0',
        'button_margin_top' => '5',
        'button_margin_right' => '0',
        'button_margin_bottom' => '0',
        'button_position' => 'center'
    ),
    'custom_css' => ".arm_form_input_container_rememberme{margin-top:-10px !important;margin-left:15px !important;}.arm_form_field_submit_button:hover{border:0px; !important;}.arm_form_heading_container{margin-bottom:40px !important;}"
);

$forms['arm_form_settings'] = maybe_serialize($form_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'label' => __('Username','ARMember'),
    'placeholder' => __('Username','ARMember'),
    'type' => 'text',
    'meta_key' => 'user_login',
    'hide_username' => '0',
    'required' => '1',
    'blank_message' => __('Username can not be left blank','ARMember'),
    'invalid_message' => __('Please enter valid username','ARMember'),
    'default_field' => '1',
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'first_name',
    'label' => __('First Name','ARMember'),
    'placeholder' => __('First Name','ARMember'),
    'type' => 'text',
    'meta_key' => 'first_name',
    'required' => '1',
    'hide_firstname' => '0',
    'blank_message' => __('First Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'first_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'last_name',
    'label' => __('Last Name','ARMember'),
    'placeholder' => __('Last Name','ARMember'),
    'type' => 'text',
    'meta_key' => 'last_name',
    'required' => '1',
    'hide_lastname' => '0',
    'blank_message' => __('Last Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'last_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_email',
    'label' => __('Email Address','ARMember'),
    'placeholder' => __('Email Address','ARMember'),
    'type' => 'email',
    'meta_key' => 'user_email',
    'required' => '1',
    'blank_message' => __('Email Address can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid email address.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => 'user_email',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_pass',
    'label' => __("Password",'ARMember'),
    'placeholder' => __("Password",'ARMember'),
    'type' => 'password',
    'options' => array(
        'strength_meter' => '1',
        'strong_password' => '0',
        'minlength' => '6',
        'maxlength' => '',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid password.','ARMember')
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 5,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'submit',
    'label' => __('Submit','ARMember'),
    'type' => 'submit',
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 6,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);
unset($form_id);
unset($forms);

/* Registration Template */

$forms = array();
$forms['arm_form_label'] = __('PLEASE LOGIN', 'ARMember');
$forms['arm_form_title'] = __('PLEASE LOGIN', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-login-3';
$forms['arm_set_name'] = __('Template 3', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 3;
$forms['arm_set_id'] = '-3';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings = array();
$form_settings = array();
$form_settings['display_direction'] = 'vertical';
$form_settings['redirect_type'] = 'page';
$form_settings['redirect_page'] = $login_redirect_id;
$form_settings['redirect_url'] = '';
$form_settings['show_rememberme'] = '1';
$form_settings['show_registration_link'] = '1';
$form_settings['registration_link_label'] = 'CREATE ACCOUNT';
$form_settings['registration_link_type'] = 'page';
$form_settings['registration_link_type_modal'] = '1';
$form_settings['registration_link_type_page'] = $register_page_id;
$form_settings['show_forgot_password_link'] = '1';
$form_settings['forgot_password_link_label'] = 'Forgot Password';
$form_settings['forgot_password_link_type'] = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top'] = '-132';
$form_settings['forgot_password_link_margin']['left'] = '320';
$form_settings['forgot_password_link_margin']['right'] = '0';
$form_settings['registration_link_margin']['top'] = '5';
$form_settings['registration_link_margin']['bottom'] = '0';
$form_settings['registration_link_margin']['left'] = '150';
$form_settings['registration_link_margin']['right'] = '0';

$form_style = array(
    'form_layout' => 'rounded',
    'form_width' => '550',
    'form_width_type' => 'px',
    'form_border_width' => '0',
    'form_border_radius' => '25',
    'form_border_style' => 'solid',
    'form_padding_left' => '45',
    'form_padding_top' => '40',
    'form_padding_right' => '45',
    'form_padding_bottom' => '30',
    'form_position' => 'left',
    'form_bg' => '',
    'form_title_font_family' => 'Helvetica',
    'form_title_font_size' => '26',
    'form_title_font_bold' => '1',
    'form_title_font_italic' => '0',
    'form_title_font_decoration' => '',
    'form_title_position' => 'center',
    'validation_position' => 'bottom',
    'color_scheme' => 'red',
    'lable_font_color' => '#919191',
    'field_font_color' => '#242424',
    'field_border_color' => '#dbdbdb',
    'field_focus_color' => '#b8b8b8',
    'button_back_color' => '#dd2476',
    'button_back_color_gradient' => '#ff512f',
    'button_hover_color' => '#dd2476',
    'button_hover_color_gradient' => '#ff512f',
    'button_font_color' => '#ffffff',
    'button_hover_font_color' => '#ffffff',
    "login_link_font_color" => '#e65e80',
    "register_link_font_color" => '#e65e80',
    'form_title_font_color' => '#bdbdbd',
    'form_bg_color' => '#ffffff',
    'form_border_color' => '#cccccc',
    'prefix_suffix_color' => '#bababa',
    'error_font_color' => '#ffffff',
    'error_field_border_color' => '#f05050',
    'error_field_bg_color' => '#e6594d',
    'field_width' => '100',
    'field_width_type' => '%',
    'field_height' => '45',
    'field_spacing' => '5',
    'field_border_width' => '2',
    'field_border_radius' => '40',
    'field_border_style' => 'solid',
    'field_font_family' => 'Helvetica',
    'field_font_size' => '14',
    'field_font_bold' => '0',
    'field_font_italic' => '0',
    'field_font_decoration' => '',
    'field_position' => 'left',
    'rtl' => '0',
    'label_width' => '250',
    'label_width_type' => 'px',
    'label_position' => 'block',
    'label_align' => 'left',
    'label_hide' => '1',
    'label_font_family' => 'Helvetica',
    'label_font_size' => '16',
    'description_font_size' => '16',
    'label_font_bold' => '0',
    'label_font_italic' => '0',
    'label_font_decoration' => '',
    'button_width' => '150',
    'button_width_type' => 'px',
    'button_height' => '50',
    'button_height_type' => 'px',
    'button_border_radius' => '50',
    'button_style' => 'flat',
    'button_font_family' => 'Helvetica',
    'button_font_size' => '18',
    'button_font_bold' => '1',
    'button_font_italic' => '0',
    'button_font_decoration' => '',
    'button_margin_left' => '0',
    'button_margin_top' => '5',
    'button_margin_right' => '0',
    'button_margin_bottom' => '0',
    'button_position' => 'center'
);

$form_settings['style'] = $form_style;

$form_settings['custom_css'] = ".arm_form_input_container_rememberme{margin-top:-10px !important;margin-left:15px !important;}.arm_form_field_submit_button:hover{border:0px; !important;}.arm_form_heading_container{margin-bottom:40px !important;}";

$form_template_settings = $form_settings;
$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;
$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => __('Username','ARMember'),
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'hide_username' => 0,
    'meta_key' => 'user_login',
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'user_pass',
    'label' => __('Password', 'ARMember'),
    'placeholder' => __('Password','ARMember'),
    'type' => 'password',
    'default_field' => '1',
    'options' => array(
        'strength_meter' => '0',
        'strong_password' => '0',
        'minlength' => '1',
        'maxlength' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid password', 'ARMember')
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'rememberme',
    'type' => 'rememberme',
    'default_field' => '1',
    'label' => __('Remember me', 'ARMember'),
    'meta_key' => 'rememberme',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'rememberme',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'LOGIN',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Login Form Template End */

/* Forgot Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Forgot Password', 'ARMember');
$forms['arm_form_title'] = __('Forgot Password', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-forgot-password-3';
$forms['arm_set_name'] = __('Template 3', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 3;
$forms['arm_set_id'] = '-3';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = __('Please enter your email address or username below.','ARMember');

$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => __('Username', 'ARMember'),
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'hide_username' => 0,
    'meta_key' => 'user_login',
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Forgot Password Form End */

/* Change Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Change Password', 'ARMember');
$forms['arm_form_title'] = __('Change Password', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-change-password-3';
$forms['arm_set_name'] = __('Template 3', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 3;
$forms['arm_set_id'] = '-3';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message'] = __('Your password changed successfully.','ARMember');


$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_pass',
    'type' => 'password',
    'default_field' => '1',
    'label' => __('New Password', 'ARMember'),
    'placeholder' => __('New Password', 'ARMember'),
    'options' => array(
        'minlength' => '6',
        'maxlength' => '',
        'strength_meter' => '1',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'required' => '1',
    'meta_key' => 'user_pass',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'repeat_pass',
    'type' => 'repeat_pass',
    'default_field' => '1',
    'label' => __('Confirm Password', 'ARMember'),
    'placeholder' => __('Confirm Password', 'ARMember'),
    'required' => '1',
    'meta_key' => 'repeat_pass',
    'blank_message' => __('Confirm Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Passwords don\'t match.','ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => $form_field_id
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'repeat_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);
$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);
unset($form_field_id);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);
unset($form_template_settings);

/* Third Set End */

/*Fourth set Start */

$forms = array();
$forms['arm_form_label'] = __('Template 4','ARMember');
$forms['arm_form_title'] = __('Please Signup', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-registration-4';
$forms['arm_set_name'] = __('Template 4','ARMember');
$forms['arm_is_default'] = 1;
$forms['arm_is_template'] = 1;
$forms['arm_ref_template'] = 4;
$forms['arm_set_id'] = 0;
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_settings = array(
    'redirect_type' => 'page',
    'redirect_page' => $reg_redirect_id,
    'redirect_url' => '',
    'auto_login' => '1',
    'style' => array(
        'form_layout' => 'iconic',
        'form_width' => '600',
        'form_width_type' => 'px',
        'form_border_width' => '0',
        'form_border_radius' => '8',
        'form_border_style' => 'solid',
        'form_padding_left' => '30',
        'form_padding_top' => '30',
        'form_padding_bottom' => '30',
        'form_padding_right' => '30',
        'form_position' => 'left',
        'form_bg' => '',
        'form_title_font_family' => 'Helvetica',
        'form_title_font_size' => '28',
        'form_title_font_bold' => '1',
        'form_title_font_italic' => '0',
        'form_title_font_decoration' => '',
        'form_title_position' => 'center',
        'validation_position' => 'bottom',
        'color_scheme' => 'green',
        'lable_font_color' => '#919191',
        'field_font_color' => '#242424',
        'field_border_color' => '#e6e6e6',
        'field_focus_color' => '#27c24c',
        'field_bg_color' => '#f0f0f0',
        'button_back_color' => '#27c24c',
        'button_font_color' => '#fcfcfc',
        'button_hover_color' => '#29cc50',
        'button_hover_font_color' => '#ffffff',
        'form_title_font_color' => '#a1a1a1',
        'form_bg_color' => '#ffffff',
        'form_border_color' => '#cccccc',
        'prefix_suffix_color' => '#bababa',
        'error_font_color' => '#ffffff',
        'error_field_border_color' => '#f05050',
        'error_field_bg_color' => '#e6594d',
        'login_link_font_color' => '#27c24c',
        'register_link_font_color' => '#27c24c',
        'field_width' => '100',
        'field_width_type' => '%',
        'field_height' => '40',
        'field_spacing' => '5',
        'field_border_width' => '1',
        'field_border_radius' => '4',
        'field_border_style' => 'solid',
        'field_font_family' => 'Helvetica',
        'field_font_size' => '14',
        'field_font_bold' => '0',
        'field_font_italic' => '0',
        'field_font_decoration' => '',
        'field_position' => 'left',
        'rtl' => '0',
        'label_width' => '250',
        'label_width_type' => 'px',
        'label_position' => 'block',
        'label_align' => 'left',
        'label_hide' => '1',
        'label_font_family' => 'Helvetica',
        'label_font_size' => '16',
        'description_font_size' => '16',
        'label_font_bold' => '0',
        'label_font_italic' => '0',
        'label_font_decoration' => '',
        'button_width' => '370',
        'button_width_type' => 'px',
        'button_height' => '45',
        'button_height_type' => 'px',
        'button_border_radius' => '4',
        'button_style' => 'flat',
        'button_font_family' => 'Helvetica',
        'button_font_size' => '18',
        'button_font_bold' => '1',
        'button_font_italic' => '0',
        'button_font_decoration' => '',
        'button_margin_left' => '0',
        'button_margin_top' => '10',
        'button_margin_right' => '0',
        'button_margin_bottom' => '0',
        'button_position' => 'center'
    )
);

$forms['arm_form_settings'] = maybe_serialize($form_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'label' => __('Username','ARMember'),
    'placeholder' => __('Username','ARMember'),
    'type' => 'text',
    'meta_key' => 'user_login',
    'required' => '1',
    'hide_username' => '0',
    'blank_message' => __('Username can not be left blank','ARMember'),
    'invalid_message' => __('Please enter valid username','ARMember'),
    'default_field' => '1',
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'first_name',
    'label' => __('First Name','ARMember'),
    'placeholder' => __('First Name','ARMember'),
    'type' => 'text',
    'meta_key' => 'first_name',
    'required' => '1',
    'hide_firstname' => '0',
    'blank_message' => __('First Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'first_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'last_name',
    'label' => __('Last Name','ARMember'),
    'placeholder' => __('Last Name','ARMember'),
    'type' => 'text',
    'meta_key' => 'last_name',
    'required' => '1',
    'hide_lastname' => '0',
    'blank_message' => __('Last Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'last_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_email',
    'label' => __('Email Address','ARMember'),
    'placeholder' => __('Email Address','ARMember'),
    'type' => 'email',
    'meta_key' => 'user_email',
    'required' => '1',
    'blank_message' => __('Email Address can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid email address.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => 'user_email',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_pass',
    'label' => __("Password",'ARMember'),
    'placeholder' => __("Password",'ARMember'),
    'type' => 'password',
    'options' => array(
        'strength_meter' => '1',
        'strong_password' => '0',
        'minlength' => '6',
        'maxlength' => '',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid password.','ARMember')
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 5,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'submit',
    'label' => __('Submit','ARMember'),
    'type' => 'submit',
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 6,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);
unset($form_id);
unset($forms);


/* Login Form Template Start */
$forms = array();
$forms['arm_form_label'] = __('Please Login', 'ARMember');
$forms['arm_form_title'] = __('Please Login', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-login-4';
$forms['arm_set_name'] = __('Template 4', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 4;
$forms['arm_set_id'] = '-4';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings = array();
$form_settings = array();
$form_settings['display_direction'] = 'vertical';
$form_settings['redirect_type'] = 'page';
$form_settings['redirect_page'] = $login_redirect_id;
$form_settings['redirect_url'] = '';
$form_settings['show_rememberme'] = '0';
$form_settings['show_registration_link'] = '1';
$form_settings['registration_link_label'] = 'Create User';
$form_settings['registration_link_type'] = 'page';
$form_settings['registration_link_type_modal'] = '1';
$form_settings['registration_link_type_page'] = $register_page_id;
$form_settings['show_forgot_password_link'] = '1';
$form_settings['forgot_password_link_label'] = 'Forgot Password';
$form_settings['forgot_password_link_type'] = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top'] = '0';
$form_settings['forgot_password_link_margin']['left'] = '0';
$form_settings['forgot_password_link_margin']['right'] = '0';
$form_settings['registration_link_margin']['top'] = '-40';
$form_settings['registration_link_margin']['bottom'] = '0';
$form_settings['registration_link_margin']['left'] = '280';
$form_settings['registration_link_margin']['right'] = '0';

$form_style = array(
    'social_btn_position' => 'bottom',
    'social_btn_type' => 'horizontal',
    'social_btn_align' => 'center',
    'enable_social_btn_separator' => '1',
    'social_btn_separator' => '<center>OR</center>',
    'form_layout' => 'iconic',
    'form_width' => '550',
    'form_width_type' => 'px',
    'form_border_width' => '1',
    'form_border_radius' => '8',
    'form_border_style' => 'solid',
    'form_padding_left' => '80',
    'form_padding_top' => '30',
    'form_padding_right' => '80',
    'form_padding_bottom' => '30',
    'form_position' => 'left',
    'form_bg' => '',
    'form_title_font_family' => 'Helvetica',
    'form_title_font_size' => '28',
    'form_title_font_bold' => '1',
    'form_title_font_italic' => '0',
    'form_title_font_decoration' => '',
    'form_title_position' => 'center',
    'validation_position' => 'bottom',
    'color_scheme' => 'green',
    'lable_font_color' => '#919191',
    'field_font_color' => '#242424',
    'field_border_color' => '#e6e6e6',
    'field_focus_color' => '#27c24c',
    'field_bg_color' => '#f0f0f0',
    'button_back_color' => '#27c24c',
    'button_font_color' => '#fcfcfc',
    'button_hover_color' => '#29cc50',
    'button_hover_font_color' => '#ffffff',
    'form_title_font_color' => '#a1a1a1',
    'form_bg_color' => '#ffffff',
    'form_border_color' => '#cccccc',
    'prefix_suffix_color' => '#bababa',
    'error_font_color' => '#ffffff',
    'error_field_border_color' => '#f05050',
    'error_field_bg_color' => '#e6594d',
    'login_link_font_color' => '#27c24c',
    'register_link_font_color' => '#27c24c',
    'field_width' => '100',
    'field_width_type' => '%',
    'field_height' => '40',
    'field_spacing' => '5',
    'field_border_width' => '1',
    'field_border_radius' => '4',
    'field_border_style' => 'solid',
    'field_font_family' => 'Helvetica',
    'field_font_size' => '14',
    'field_font_bold' => '0',
    'field_font_italic' => '0',
    'field_font_decoration' => '',
    'field_position' => 'center',
    'rtl' => '0',
    'label_width' => '250',
    'label_width_type' => 'px',
    'label_position' => 'block',
    'label_align' => 'left',
    'label_hide' => '1',
    'label_font_family' => 'Helvetica',
    'label_font_size' => '16',
    'description_font_size' => '16',
    'label_font_bold' => '0',
    'label_font_italic' => '0',
    'label_font_decoration' => '',
    'button_width' => '370',
    'button_width_type' => 'px',
    'button_height' => '45',
    'button_height_type' => 'px',
    'button_border_radius' => '4',
    'button_style' => 'flat',
    'button_font_family' => 'Helvetica',
    'button_font_size' => '18',
    'button_font_bold' => '1',
    'button_font_italic' => '0',
    'button_font_decoration' => '',
    'button_margin_left' => '0',
    'button_margin_top' => '10',
    'button_margin_right' => '0',
    'button_margin_bottom' => '0',
    'button_position' => 'center'
);

$form_custom_style = '.arm_form_field_container .arm_forgotpassword_link, .arm_form_field_container.arm_forgotpassword_link, .arm_forgotpassword_link { width: auto !important; }
.arm_editor_form_fileds_wrapper .arm_login_links_wrapper, .arm_login_links_wrapper{ width:auto !important ; }';

$form_settings['style'] = $form_style;
$form_settings['custom_css'] = $form_custom_style;

$form_template_settings = $form_settings;
$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;
$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => __('Username', 'ARMember'),
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'meta_key' => 'user_login',
    'hide_username' => 0,
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'user_pass',
    'label' => __('Password', 'ARMember'),
    'placeholder' => __('Password', 'ARMember'),
    'type' => 'password',
    'default_field' => '1',
    'options' => array(
        'strength_meter' => '0',
        'strong_password' => '0',
        'minlength' => '1',
        'maxlength' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid password', 'ARMember')
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'rememberme',
    'type' => 'rememberme',
    'default_field' => '1',
    'default_val' => 'forever',
    'label' => __('Remember me', 'ARMember'),
    'meta_key' => 'rememberme',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'rememberme',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'LOGIN',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Login Form Template End */

/* Forgot Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Forgot Password', 'ARMember');
$forms['arm_form_title'] = __('Forgot Password', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-forgot-password-4';
$forms['arm_set_name'] = __('Template 4', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 4;
$forms['arm_set_id'] = '-4';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = __('<center>Please enter your email address or username below.</center>','ARMember');

$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => __('Username', 'ARMember'),
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'hide_username' => 0,
    'meta_key' => 'user_login',
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Forgot Password Form End */

/* Change Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Change Password', 'ARMember');
$forms['arm_form_title'] = __('Change Password', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-change-password-4';
$forms['arm_set_name'] = __('Template 4', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 4;
$forms['arm_set_id'] = '-4';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message'] = __('Your password changed successfully.','ARMember');


$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_pass',
    'type' => 'password',
    'default_field' => '1',
    'label' => __('New Password', 'ARMember'),
    'placeholder' => __('New Password', 'ARMember'),
    'options' => array(
        'minlength' => '6',
        'maxlength' => '',
        'strength_meter' => '1',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'required' => '1',
    'meta_key' => 'user_pass',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'repeat_pass',
    'type' => 'repeat_pass',
    'default_field' => '1',
    'label' => __('Confirm Password', 'ARMember'),
    'placeholder' => __('Confirm Password', 'ARMember'),
    'required' => '1',
    'meta_key' => 'repeat_pass',
    'blank_message' => __('Confirm Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Passwords don\'t match.','ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => $form_field_id
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'repeat_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);
$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);
unset($form_field_id);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);
unset($form_template_settings);

/* Change Password Form End */



/*Fourth set End */

/* Fifth set Start */

$forms = array();
$forms['arm_form_label'] = __('Template 5','ARMember');
$forms['arm_form_title'] = __('Please Signup', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-registration-5';
$forms['arm_set_name'] = __('Template 5','ARMember');
$forms['arm_is_default'] = 1;
$forms['arm_is_template'] = 1;
$forms['arm_ref_template'] = 5;
$forms['arm_set_id'] = 0;
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_settings = array(
    'redirect_type' => 'page',
    'redirect_page' => $reg_redirect_id,
    'redirect_url' => '',
    'auto_login' => '1',
    'style' => array(
        'form_layout' => 'iconic',
        'form_width' => '600',
        'form_width_type' => 'px',
        'form_border_width' => '0',
        'form_border_radius' => '14',
        'form_border_style' => 'solid',
        'form_padding_left' => '40',
        'form_padding_top' => '40',
        'form_padding_bottom' => '30',
        'form_padding_right' => '45',
        'form_position' => 'left',
        'form_bg' => '',
        'form_title_font_family' => 'Helvetica',
        'form_title_font_size' => '28',
        'form_title_font_bold' => '1',
        'form_title_font_italic' => '0',
        'form_title_font_decoration' => '',
        'form_title_position' => 'center',
        'validation_position' => 'bottom',
        'color_scheme' => 'purple',
        'lable_font_color' => '#919191',
        'field_font_color' => '#242424',
        'field_border_color' => '#c7c7c7',
        'field_focus_color' => '#6164c1',
        'field_bg_color' => '#ffffff',
        'button_back_color' => '#6164c1',
        'button_font_color' => '#ffffff',
        'button_hover_color' => '#8072cc',
        'button_hover_font_color' => '#ffffff',
        'form_title_font_color' => '#313131',
        'form_bg_color' => '#ffffff',
        'form_border_color' => '#cccccc',
        'prefix_suffix_color' => '#bababa',
        'error_font_color' => '#ffffff',
        'error_field_border_color' => '#f05050',
        'error_field_bg_color' => '#e6594d',
        'login_link_font_color' => '#27c24c',
        'register_link_font_color' => '#27c24c',
        'field_width' => '100',
        'field_width_type' => '%',
        'field_height' => '40',
        'field_spacing' => '13',
        'field_border_width' => '1',
        'field_border_radius' => '4',
        'field_border_style' => 'solid',
        'field_font_family' => 'Helvetica',
        'field_font_size' => '14',
        'field_font_bold' => '0',
        'field_font_italic' => '0',
        'field_font_decoration' => '',
        'field_position' => 'left',
        'rtl' => '0',
        'label_width' => '250',
        'label_width_type' => 'px',
        'label_position' => 'block',
        'label_align' => 'left',
        'label_hide' => '0',
        'label_font_family' => 'Helvetica',
        'label_font_size' => '16',
        'description_font_size' => '16',
        'label_font_bold' => '0',
        'label_font_italic' => '0',
        'label_font_decoration' => '',
        'button_width' => '250',
        'button_width_type' => 'px',
        'button_height' => '45',
        'button_height_type' => 'px',
        'button_border_radius' => '8',
        'button_style' => 'reverse_border',
        'button_font_family' => 'Helvetica',
        'button_font_size' => '18',
        'button_font_bold' => '1',
        'button_font_italic' => '0',
        'button_font_decoration' => '',
        'button_margin_left' => '0',
        'button_margin_top' => '20',
        'button_margin_right' => '0',
        'button_margin_bottom' => '10',
        'button_position' => 'center'
    )
);

$forms['arm_form_settings'] = maybe_serialize($form_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'label' => __('Username','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'user_login',
    'required' => '1',
    'hide_username' => '0',
    'blank_message' => __('Username can not be left blank','ARMember'),
    'invalid_message' => __('Please enter valid username','ARMember'),
    'default_field' => '1',
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'first_name',
    'label' => __('First Name','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'first_name',
    'required' => '1',
    'hide_firstname' => '0',
    'blank_message' => __('First Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'first_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'last_name',
    'label' => __('Last Name','ARMember'),
    'placeholder' => '',
    'type' => 'text',
    'meta_key' => 'last_name',
    'required' => '1',
    'hide_lastname' => '0',
    'blank_message' => __('Last Name can not be left blank.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'last_name',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_email',
    'label' => __('Email Address','ARMember'),
    'placeholder' => '',
    'type' => 'email',
    'meta_key' => 'user_email',
    'required' => '1',
    'blank_message' => __('Email Address can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid email address.','ARMember'),
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => 'user_email',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'user_pass',
    'label' => __("Password",'ARMember'),
    'placeholder' => '',
    'type' => 'password',
    'options' => array(
        'strength_meter' => '1',
        'strong_password' => '0',
        'minlength' => '6',
        'maxlength' => '',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.','ARMember'),
    'invalid_message' => __('Please enter valid password.','ARMember')
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 5,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);

$field_options = array(
    'id' => 'submit',
    'label' => __('Submit','ARMember'),
    'type' => 'submit',
    'default_field' => '1'
);

$fields = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 6,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => '1',
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $fields);

unset($field_options);
unset($fields);
unset($form_id);
unset($forms);


/* Login Form Template Start */
$forms = array();
$forms['arm_form_label'] = __('Please Login', 'ARMember');
$forms['arm_form_title'] = __('Please Login', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-login-5';
$forms['arm_set_name'] = __('Template 5', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 5;
$forms['arm_set_id'] = '-5';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings = array();
$form_settings = array();
$form_settings['display_direction'] = 'vertical';
$form_settings['redirect_type'] = 'page';
$form_settings['redirect_page'] = $login_redirect_id;
$form_settings['redirect_url'] = '';
$form_settings['show_rememberme'] = '0';
$form_settings['show_registration_link'] = '1';
$form_settings['registration_link_label'] = 'SIGNUP';
$form_settings['registration_link_type'] = 'page';
$form_settings['registration_link_type_modal'] = '0';
$form_settings['registration_link_type_page'] = $register_page_id;
$form_settings['show_forgot_password_link'] = '1';
$form_settings['forgot_password_link_label'] = 'Forgot Password';
$form_settings['forgot_password_link_type'] = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top'] = '0';
$form_settings['forgot_password_link_margin']['left'] = '80';
$form_settings['forgot_password_link_margin']['right'] = '0';
$form_settings['registration_link_margin']['top'] = '-40';
$form_settings['registration_link_margin']['bottom'] = '0';
$form_settings['registration_link_margin']['left'] = '280';
$form_settings['registration_link_margin']['right'] = '0';

$form_style = array(
    'social_btn_position' => 'bottom',
    'social_btn_type' => 'horizontal',
    'social_btn_align' => 'center',
    'enable_social_btn_separator' => '1',
    'social_btn_separator' => '<center>OR</center>',
    'form_layout' => 'iconic',
    'form_width' => '550',
    'form_width_type' => 'px',
    'form_border_width' => '0',
    'form_border_radius' => '14',
    'form_border_style' => 'solid',
    'form_padding_left' => '40',
    'form_padding_top' => '40',
    'form_padding_right' => '45',
    'form_padding_bottom' => '30',
    'form_position' => 'left',
    'form_bg' => '',
    'form_title_font_family' => 'Helvetica',
    'form_title_font_size' => '28',
    'form_title_font_bold' => '1',
    'form_title_font_italic' => '0',
    'form_title_font_decoration' => '',
    'form_title_position' => 'center',
    'validation_position' => 'bottom',
    'color_scheme' => 'purple',
    'lable_font_color' => '#7a7a7a',
    'field_font_color' => '#242424',
    'field_border_color' => '#c7c7c7',
    'field_focus_color' => '#6164c1',
    'field_bg_color' => '#ffffff',
    'button_back_color' => '#6164c1',
    'button_font_color' => '#ffffff',
    'button_hover_color' => '#8072cc',
    'button_hover_font_color' => '#ffffff',
    "form_title_font_color" => '#757575',
    'form_bg_color' => '#ffffff',
    'form_border_color' => '#cccccc',
    'prefix_suffix_color' => '#bababa',
    'error_font_color' => '#ffffff',
    'error_field_border_color' => '#f05050',
    'error_field_bg_color' => '#e6594d',
    'login_link_font_color' => '#6164c1',
    'register_link_font_color' => '#6164c1',
    'field_width' => '100',
    'field_width_type' => '%',
    'field_height' => '40',
    'field_spacing' => '13',
    'field_border_width' => '1',
    'field_border_radius' => '4',
    'field_border_style' => 'solid',
    'field_font_family' => 'Helvetica',
    'field_font_size' => '14',
    'field_font_bold' => '0',
    'field_font_italic' => '0',
    'field_font_decoration' => '',
    'field_position' => 'left',
    'rtl' => '0',
    'label_width' => '250',
    'label_width_type' => 'px',
    'label_position' => 'block',
    'label_align' => 'left',
    'label_hide' => '0',
    'label_font_family' => 'Helvetica',
    'label_font_size' => '16',
    'description_font_size' => '16',
    'label_font_bold' => '0',
    'label_font_italic' => '0',
    'label_font_decoration' => '',
    'button_width' => '250',
    'button_width_type' => 'px',
    'button_height' => '45',
    'button_height_type' => 'px',
    'button_border_radius' => '8',
    'button_style' => 'reverse_border',
    'button_font_family' => 'Helvetica',
    'button_font_size' => '18',
    'button_font_bold' => '1',
    'button_font_italic' => '0',
    'button_font_decoration' => '',
    'button_margin_left' => '0',
    'button_margin_top' => '10',
    'button_margin_right' => '0',
    'button_margin_bottom' => '0',
    'button_position' => 'center'
);

$form_custom_style = '.arm_form_field_container .arm_forgotpassword_link, .arm_form_field_container.arm_forgotpassword_link, .arm_forgotpassword_link { width: auto !important; }
.arm_editor_form_fileds_wrapper .arm_login_links_wrapper, .arm_login_links_wrapper{ width:auto !important ; }';

$form_settings['style'] = $form_style;
$form_settings['custom_css'] = $form_custom_style;

$form_template_settings = $form_settings;
$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;
$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'hide_username' => 0,
    'meta_key' => 'user_login',
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'user_pass',
    'label' => __('Password', 'ARMember'),
    'placeholder' => '',
    'type' => 'password',
    'default_field' => '1',
    'options' => array(
        'strength_meter' => '0',
        'strong_password' => '0',
        'minlength' => '1',
        'maxlength' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'meta_key' => 'user_pass',
    'required' => '1',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid password', 'ARMember')
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'rememberme',
    'type' => 'rememberme',
    'default_field' => '1',
    'default_val' => 'forever',
    'label' => __('Remember me', 'ARMember'),
    'meta_key' => 'rememberme',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => 'rememberme',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'LOGIN',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 4,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Login Form Template End */

/* Forgot Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Forgot Password', 'ARMember');
$forms['arm_form_title'] = __('Forgot Password', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-forgot-password-5';
$forms['arm_set_name'] = __('Template 5', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 5;
$forms['arm_set_id'] = '-5';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = __('<center>Please enter your email address or username below.</center>','ARMember');

$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_login',
    'type' => 'text',
    'default_field' => '1',
    'label' => __('Username', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '',
        'maxlength' => ''
    ),
    'required' => '1',
    'hide_username' => 0,
    'meta_key' => 'user_login',
    'blank_message' => __('Username can not be left blank.', 'ARMember'),
    'invalid_message' => __('Please enter valid username.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_login',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);

/* Forgot Password Form End */

/* Change Password Form Start */
$forms = array();
$forms['arm_form_label'] = __('Change Password', 'ARMember');
$forms['arm_form_title'] = __('Change Password', 'ARMember');
$forms['arm_form_type'] = 'template';
$forms['arm_form_slug'] = 'template-change-password-5';
$forms['arm_set_name'] = __('Template 5', 'ARMember');
$forms['arm_is_default'] = '1';
$forms['arm_is_template'] = '1';
$forms['arm_ref_template'] = 5;
$forms['arm_set_id'] = '-5';
$forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
$forms['arm_form_created_date'] = date('Y-m-d H:i:s');

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message'] = __('Your password changed successfully.','ARMember');


$forms['arm_form_settings'] = maybe_serialize($form_template_settings);

$wpdb->insert($ARMember->tbl_arm_forms, $forms);
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'user_pass',
    'type' => 'password',
    'default_field' => '1',
    'label' => __('New Password', 'ARMember'),
    'placeholder' => '',
    'options' => array(
        'minlength' => '6',
        'maxlength' => '',
        'strength_meter' => '1',
        'special' => '1',
        'numeric' => '1',
        'uppercase' => '1',
        'lowercase' => '1'
    ),
    'required' => '1',
    'meta_key' => 'user_pass',
    'blank_message' => __('Password can not be left blank.', 'ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
    'id' => 'repeat_pass',
    'type' => 'repeat_pass',
    'default_field' => '1',
    'label' => __('Confirm Password', 'ARMember'),
    'placeholder' => '',
    'required' => '1',
    'meta_key' => 'repeat_pass',
    'blank_message' => __('Confirm Password can not be left blank.', 'ARMember'),
    'invalid_message' => __('Passwords don\'t match.','ARMember'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => $form_field_id
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 2,
    'arm_form_field_slug' => 'repeat_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);
$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);
unset($form_field_id);

$field_options = array(
    'id' => 'submit',
    'type' => 'submit',
    'default_field' => '1',
    'label' => 'Submit',
    'meta_key' => ''
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 3,
    'arm_form_field_slug' => '',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => date('Y-m-d H:i:s')
);

$wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);

unset($form_field_data);
unset($field_options);
unset($forms);
unset($form_id);
unset($form_template_settings);

/* Change Password Form End */



/*Fourth set End */