<?php

global $wpdb,$ARMember;

$profile_template = isset($_REQUEST['template']) ? htmlspecialchars($_REQUEST['template']) : 'profiletemplate1';
$profile_action = htmlspecialchars($_REQUEST['action']);
$default_cover_photo = 0;

if (!wp_script_is('arm_admin_file_upload_js', 'enqueued')) {
    wp_enqueue_script('arm_admin_file_upload_js');
}

wp_enqueue_style('arm_bootstrap_all_css');

switch ($profile_template) {
    case 1:
        $temp_slug = 'profiletemplate1';
        break;

    case 2:
        $temp_slug = 'profiletemplate2';
        break;

    case 3:
        $temp_slug = 'profiletemplate3';
        break;

    case 4:
        $temp_slug = 'profiletemplate4';
        break;

    default:
        $temp_slug = 'profiletemplate1';
        break;
}

global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_members_directory, $arm_subscription_plans, $arm_member_forms;
$member_templates = $arm_members_directory->arm_get_all_member_templates();
$defaultTemplates = $arm_members_directory->arm_default_member_templates();
$tempColorSchemes = $arm_members_directory->getTemplateColorSchemes();
$subs_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
$tempColorSchemes = $arm_members_directory->getTemplateColorSchemes();
$tempColorSchemes1 = $arm_members_directory->getTemplateColorSchemes1();


$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$general_settings = $all_global_settings['general_settings'];
$enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0;

$profile_templates = array();
foreach ($defaultTemplates as $key => $template) {
    if ($template['arm_type'] == 'profile') {
        array_push($profile_templates, $template);
    }
}
?>
<?php

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
$arm_profile_before_content = $arm_profile_after_content = "";

$profile_fields_data = array();
$profile_fields_data['profile_fields'] = array(
    'user_login' => 'user_login',
    'user_email' => 'user_email',
    'first_name' => 'first_name',
    'last_name' => 'last_name'
);

$profile_fields_data['label'] = array(
    'user_login' => 'Username',
    'user_email' => 'Email Address',
    'first_name' => 'First Name',
    'last_name' => 'Last Name'
);

$profile_fields_data['default_values'] = $arm_members_directory->arm_get_profile_dummy_data();

echo "<script type='text/javascript'>";
echo "function arm_profile_editor_default_data(){";
echo "var profile_default_values = '';";
echo "profile_default_values = '".json_encode($profile_fields_data['default_values'])."';";
echo "return profile_default_values; ";
echo "}";
echo "</script>";

$options = array(
    'pagination' => 'numeric',
    'show_badges' => 1,
    'show_joining' => 1,
    'hide_empty_profile_fields' => 1,
    'color_scheme' => 'blue',
    'title_color' => '#0c7cd5',
    'subtitle_color' => '#575d70',
    'border_color' => '#0c7cd5',
    'button_color' => '',
    'button_font_color' => '',
    'tab_bg_color' => '',
    'tab_link_color' => '',
    'tab_link_hover_color' => '',
    'tab_link_bg_color' => '',
    'tab_link_hover_bg_color' => '',
    'link_color' => '',
    'link_hover_color' => '',
    'content_font_color' => '#616175',
    'box_bg_color' => '',
    'title_font' => array(
        'font_family' => 'Open Sans Semibold',
        'font_size' => '26',
        'font_bold' => 1,
        'font_italic' => 0,
        'font_decoration' => '',
    ),
    'subtitle_font' => array(
        'font_family' => 'Open Sans Semibold',
        'font_size' => '16',
        'font_bold' => 0,
        'font_italic' => 0,
        'font_decoration' => '',
    ),
    'button_font' => array(
        'font_family' => 'Open Sans Semibold',
        'font_size' => '16',
        'font_bold' => 0,
        'font_italic' => 0,
        'font_decoration' => '',
    ),
    'tab_link_font' => array(
        'font_family' => 'Open Sans Semibold',
        'font_size' => '16',
        'font_bold' => 1,
        'font_italic' => 0,
        'font_decoration' => '',
    ),
    'content_font' => array(
        'font_family' => 'Open Sans Semibold',
        'font_size' => '16',
        'font_bold' => 0,
        'font_italic' => 0,
        'font_decoration' => '',
    ),
    'default_cover' => MEMBERSHIP_IMAGES_URL.'/profile_default_cover.png',
    'custom_css' => '',
);


$arm_template_title = "";
$display_joining_date = $options['show_joining'];
$display_member_badges = $options['show_badges'];
$display_admin_profile = 0;
$subscription_plans = array();
$template_id = 0;
$is_default_template = 0;
$hide_empty_profile_fields = 0;
$default_data = array();
if((isset($_GET['action']) && $_GET['action'] == 'edit_profile') || (isset($_GET['action']) && $_GET['action'] == 'duplicate_profile')){
    $template_id = intval($_GET['id']);
    $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".$ARMember->tbl_arm_member_templates."` WHERE arm_type = %s and arm_id = %d",'profile',$template_id) );
    if( $data == '' || empty($data) ){
        wp_redirect(admin_url('admin.php?page=arm_profiles_directories'));
        exit;
    }
    $arm_template_title = !empty($data->arm_title) ? $data->arm_title : '';
    $subscription_plans = ( isset($data->arm_subscription_plan) && $data->arm_subscription_plan != '' ) ? explode(',',$data->arm_subscription_plan) : array();
    $default_data = $data;
    $temp_slug = $data->arm_slug;
    $options = maybe_unserialize($data->arm_options);
    $default_data->arm_options = maybe_unserialize($options);
    
    $display_admin_profile = $data->arm_enable_admin_profile;
    $is_default_template = $data->arm_default;
    $display_member_badges = isset($options['show_badges']) && $options['show_badges'] != '' ? $options['show_badges'] : 0;
    $display_joining_date = isset($options['show_joining']) && $options['show_joining'] != '' ? $options['show_joining'] : 0;
    $default_cover_photo = isset($options['default_cover_photo']) && $options['default_cover_photo'] != '' ? $options['default_cover_photo'] : 0;
    $arm_profile_before_content = $data->arm_html_before_fields;
    $arm_profile_after_content = $data->arm_html_after_fields;
    $profile_fields_data['profile_fields'] = isset($options['profile_fields'] ) && $options['profile_fields'] != '' ? $options['profile_fields'] : array();
    $profile_fields_data['label'] = isset($options['label']) && $options['label'] != '' ? $options['label'] : array();
    $hide_empty_profile_fields = isset($options['hide_empty_profile_fields']) ? $options['hide_empty_profile_fields'] : 1;
}




$options['color_scheme'] = isset($options['color_scheme']) && $options['color_scheme'] != '' ? $options['color_scheme'] : 'blue'; 

$options = apply_filters('arm_profile_default_options_outside',$options);

?>
<div class="wrap arm_page arm_profiles_main_wrapper armPageContainer">
    <div class="arm_toast_container" id="arm_toast_container"></div>
    <div class="content_wrapper arm_profiles_directories_container" id="content_wrapper" style="min-height: 500px; width: 100%; float:left;">
        <div class="page_title"><?php _e('Profiles & Directories', 'ARMember'); ?></div>
        <div class="armclear"></div>
        <?php
        $backToListingIcon = MEMBERSHIP_IMAGES_URL . '/back_to_listing_arrow.png';
        if (is_rtl()) {
            $backToListingIcon = MEMBERSHIP_IMAGES_URL . '/back_to_listing_arrow_right.png';
            $arm_profile_form_rtl = 'arm_profile_form_rtl'; 
        }
        ?>
        <input type="hidden" id="arm_default_profile_data" value='<?php echo esc_attr(json_encode($default_data)); ?>' />
        <form name="arm_add_profile_temp_form" class="arm_add_profile_temp_form" id="arm_add_profile_temp_form" onSubmit="return false;" method="POST" action="#">
            <input type="hidden" name="template_options[user_detail_width]" id="arm_user_meta_detail_div" value="">
            <input type="hidden" name="id" id="arm_profile_template_id" value="<?php echo $profile_template; ?>">
            <input type="hidden" name="template_id" id="template_id" value="<?php echo $template_id; ?>" />
            <input type="hidden" name="arf_profile_action" id="arf_profile_action" value="<?php echo isset($_GET['action']) ? $_GET['action'] : 'add_profile'; ?>" />
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
            <div class="arm_sticky_top_belt" id="arm_sticky_top_belt">
                <div class="arm_belt_box arm_template_action_belt">
                    <div class="arm_belt_block">
                        <a href="<?php echo admin_url('admin.php?page=' . $arm_slugs->profiles_directories); ?>" class="armemailaddbtn"><img src="<?php echo $backToListingIcon; ?>"/><?php _e('Back to listing', 'ARMember'); ?></a>
                    </div>
                    <div class="arm_belt_block arm_temp_action_btns" align="<?php echo (is_rtl()) ? 'left' : 'right'; ?>">
                        <button type="button" class="arm_save_btn arm_add_profile_template_submit" data-type="profile"><?php _e('Save', 'ARMember'); ?></button>
                    </div>
                    <div class="armclear"></div>
                </div>
            </div>
            <div class="arm_belt_box arm_template_action_belt" style="padding: 10px 15px; margin-bottom: 30px;">
                <div class="arm_belt_block" style="font-size: 20px; vertical-align: middle;">
                    <?php if($_GET['action'] == 'edit_profile'){
                        _e('Edit Profile Template', 'ARMember');
                    } else if($_GET['action'] == 'duplicate_profile') {
                        _e('Copy Profile Template', 'ARMember');
                    } else {
                        _e('Add Profile Template', 'ARMember');
                    }
                    ?>
                </div>
                <div class="arm_belt_block arm_temp_action_btns" align="<?php echo (is_rtl()) ? 'left' : 'right'; ?>">
                    <button type="button" class="arm_save_btn arm_add_profile_template_submit" data-type="profile"><?php _e('Save', 'ARMember'); ?></button>
                    <button type="button" class="arm_save_btn arm_add_profile_template_reset" id="arm_add_profile_template_reset" data-type="profile" style="background-color: #00b2f0; border:1px solid #00b2f0;"><?php _e('Reset', 'ARMember'); ?></button>
                </div>
                <div class="armclear"></div>
            </div>

            <div class="arm_profile_template_name_div arm_form_fields_wrapper">
                <label class="arm_opt_title"><?php _e('Profile Template Name', 'ARMember'); ?></label>
                <input type="text" name="arm_profile_template_name" class="arm_form_input_box" value="<?php echo $arm_template_title; ?>">
            </div>

            <div class="arm_profile_editor_left_div">
                <div class="arm_profile_belt">
                    <div id="" class="arm_profile_belt_icon desktop selected" title="<?php _e('Desktop View', 'ARMember'); ?>" data-type="desktop"></div>
                    <div id="" class="arm_profile_belt_icon tab" title="<?php _e('Tablet View', 'ARMember'); ?>" data-type="tab"></div>
                    <div id="" class="arm_profile_belt_icon mobile" title="<?php _e('Mobile View', 'ARMember'); ?>" data-type="mobile"></div>
                    <div id="arf_profile_css_settings_popup" class="arm_profile_belt_right_icon" title="<?php _e('Add Custom CSS', 'ARMember'); ?>">
                        <span class="arm_profile_template_belt_icon custom_css"></span>
                    </div>
                    <div id="arm_profile_settings_popup" class="arm_profile_belt_right_icon"  title="<?php _e('Change Profile Template', 'ARMember'); ?>">
                        <span class="arm_profile_template_belt_icon select_template"></span>
                        <div class="arm_profile_settings_popup" id="arm_profile_settings_popup_div" style="display:none;">
                            <div class="arm_profile_settings_popup_div_title">
                                <?php _e('Select Template', 'ARMember'); ?>
                                <span class='arm_profile_settings_popup_close_button' data-id='arm_profile_settings_popup_div'></span>
                            </div>
                            <input type="hidden" name="arm_profile_template" value="<?php echo $temp_slug; ?>" id="arm_profile_template" />
                            <dl class="arm_selectbox column_level_dd" style="width:100%;">
                                <dt style="width:100%;"><span><?php echo (isset($profile_templates) && is_array($profile_templates) && count($profile_templates) > 0 ) ? $profile_templates[0]['arm_title'] : 'Profile Template 1'; ?></span><input type="text" style="display:none;" class="arm_autocomplete" readonly="readonly"><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul data-id="arm_profile_template" style="display: none;">
                                        <?php
                                        if (isset($profile_templates) && is_array($profile_templates) && count($profile_templates) > 0) {
                                            ?>
                                            <?php foreach ($profile_templates as $k => $template) { ?>
                                                <li data-label="<?php echo $template['arm_title']; ?>" data-value="<?php echo $template['arm_slug']; ?>"><span class="arm_selectbox_option_list"><?php echo $template['arm_title']; ?></span><img class="arm_profile_template_image" src="<?php echo MEMBERSHIP_VIEWS_URL . '/templates/' . $template['arm_slug'] . '.png'; ?>" width="50" height="50" /></li>
                                                <?php
                                            }
                                        }
                                        ?>       
                                    </ul>
                                </dd>
                            </dl>
                            <div class="arm_accordion_separator"></div>
                            <div class="arm_profile_template_settings_popup_footer">
                                <button type="button" class="armemailaddbtn" id="arm_profile_template_settings_close"><?php _e('Apply','ARMember') ?></button>
                            </div>
                        </div>
                    </div>

                    <div id="arm_profile_font_settings_popup" class="arm_profile_belt_right_icon" title="<?php _e('Change Font Settings', 'ARMember'); ?>">
                        <span class="arm_profile_template_belt_icon font_setting" ></span>
                        <div class="arm_profile_settings_popup" id="arm_profile_font_settings_popup_div" style="display:none;">
                            <div class="arm_profile_font_settings_popup_title">
                                <?php _e('Font Settings', 'ARMember'); ?>
                                <span class='arm_profile_settings_popup_close_button' data-id='arm_profile_font_settings_popup_div'></span>    
                            </div>
                            <div class="arm_profile_font_settings_popup_inner_div">
                                <?php
                                $fontOptions = array(
                                    'title_font' => __('Title Font', 'ARMember'),
                                    'subtitle_font' => __('Sub Title Font', 'ARMember'),
                                    'content_font' => __('Content Font', 'ARMember'),
                                );
                                ?>
                                <?php foreach ($fontOptions as $key => $value): ?>
                                    <div class="arm_temp_font_opts_box">
                                        <div class="arm_opt_label"><?php echo $value; ?></div>
                                        <div class="arm_temp_font_opts">
                                            <input type="hidden" id="arm_template_font_family_<?php echo $key; ?>" name="template_options[<?php echo $key; ?>][font_family]" value="<?php echo ($_GET['action'] == 'edit_profile' && $options[$key]['font_family'] != '' ) ? $options[$key]['font_family'] : 'Helvetica' ?> "/>
                                            <dl class="arm_selectbox column_level_dd">
                                                <dt><span><?php echo ($_GET['action'] == 'edit_profile' ) ? $options[$key]['font_family'] : 'Helvetica' ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete" readonly="readonly"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                <dd>
                                                    <ul data-id="arm_template_font_family_<?php echo $key; ?>"><?php echo $arm_member_forms->arm_fonts_list(); ?></ul>
                                                </dd>
                                            </dl>
                                            <?php
                                                $fontSize = $options[$key]['font_size'];
                                            ?>
                                            <input type="hidden" id="arm_template_font_size_<?php echo $key; ?>" name="template_options[<?php echo $key; ?>][font_size]" value="<?php echo $fontSize; ?>"/>
                                            <dl class="arm_selectbox column_level_dd">
                                                <dt style="width:75px;min-width: 75px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" readonly="readonly"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                <dd>
                                                    <ul data-id="arm_template_font_size_<?php echo $key; ?>">
                                                        <?php for ($i = 8; $i < 41; $i++): ?>
                                                            <li data-label="<?php echo $i; ?> px" data-value="<?php echo $i; ?>"><?php echo $i; ?> px</li>
                                                        <?php endfor; ?>
                                                    </ul>
                                                </dd>
                                            </dl>
                                            <div class="arm_font_style_options arm_template_font_style_options">
                                                <?php
                                                    $bold_cls = isset($options[$key]['font_bold']) && $options[$key]['font_bold'] == 1 ? 'arm_style_active' : '';
                                                    $italic_cls = isset($options[$key]['font_italic']) && $options[$key]['font_italic'] == 1 ? 'arm_style_active' : '';
                                                    $underline_cls = isset($options[$key]['font_decoration']) && $options[$key]['font_decoration'] == 'underline' ? 'arm_style_active' : '';
                                                    $strike_cls = isset($options[$key]['font_decoration']) && $options[$key]['font_decoration'] == 'line-through' ? 'arm_style_active' : '';
                                                ?>
                                                <label class="arm_font_style_label <?php echo $bold_cls; ?>" data-value="bold" data-field="arm_template_font_bold_<?php echo $key; ?>"><i class="armfa armfa-bold"></i></label>
                                                <input type="hidden" name="template_options[<?php echo $key; ?>][font_bold]" id="arm_template_font_bold_<?php echo $key; ?>" class="arm_template_font_bold_<?php echo $key; ?>" value="<?php echo $options[$key]['font_bold']; ?>" />
                                                <label class="arm_font_style_label <?php echo $italic_cls; ?>" data-value="italic" data-field="arm_template_font_italic_<?php echo $key; ?>"><i class="armfa armfa-italic"></i></label>
                                                <input type="hidden" name="template_options[<?php echo $key; ?>][font_italic]" id="arm_template_font_italic_<?php echo $key; ?>" class="arm_template_font_italic_<?php echo $key; ?>" value="<?php echo $options[$key]['font_italic']; ?>" />
                                                <label class="arm_font_style_label arm_decoration_label <?php echo $underline_cls; ?>" data-value="underline" data-field="arm_template_font_decoration_<?php echo $key; ?>"><i class="armfa armfa-underline"></i></label>
                                                <label class="arm_font_style_label arm_decoration_label  <?php echo $strike_cls; ?>" data-value="line-through" data-field="arm_template_font_decoration_<?php echo $key; ?>"><i class="armfa armfa-strikethrough"></i></label>
                                                <input type="hidden" name="template_options[<?php echo $key; ?>][font_decoration]" id="arm_template_font_decoration_<?php echo $key; ?>" class="arm_template_font_decoration_<?php echo $key; ?>" value="" />
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php do_action('arm_profile_font_settings_outside',$options); ?>
                                <div class="arm_profile_font_settings_popup_footer">
                                    <button type="button" class="armemailaddbtn" id="arm_profile_font_settings_close"><?php _e('Apply','ARMember') ?></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="arm_profile_settings_color_popup" class="arm_profile_belt_right_icon" title="<?php _e('Change Color Scheme', 'ARMember'); ?>">

                        <span class="arm_profile_template_belt_icon color_settings" ></span>
                        <div class="arm_profile_settings_popup" id="arm_profile_settings_color_popup_div">

                            <div class="arm_profile_clor_scheme_div c_schemes">
                                <span class="arm_profile_color_scheme_title">
                                    <?php _e('Color Scheme', 'ARMember'); ?>
                                    <span class='arm_profile_settings_popup_close_button' data-id='arm_profile_settings_color_popup_div'></span>
                                </span>
                                <?php foreach ($tempColorSchemes as $color => $color_opt): ?>
                                    <?php
                                        $activeClass = isset($options['color_scheme']) && $options['color_scheme'] == $color ? 'arm_color_box_active' : '';
                                    ?>
                                    <label class="arm_profile_temp_color_scheme_block arm_temp_color_scheme_block_<?php echo $color; ?> <?php echo $activeClass; ?>">
                                        <span style="background-color:<?php echo $color_opt['button_color']; ?>;"></span>
                                        <span style="background-color:<?php echo $color_opt['tab_bg_color']; ?>;"></span>
                                        <input type="radio" id="arm_temp_color_radio_<?php echo $color; ?>" name="template_options[color_scheme]" value="<?php echo $color; ?>" <?php checked($color,$options['color_scheme']); ?> class="arm_temp_color_radio" data-type="profile" />
                                    </label>
                                <?php endforeach; ?>
                                <label class="arm_temp_color_scheme_block arm_temp_color_scheme_block_custom <?php echo isset($options['color_scheme']) && $options['color_scheme'] == 'custom' ? 'arm_color_box_active' : ''; ?>">
                                    <input type="radio" id="arm_temp_color_radio_custom_for_profile" name="template_options[color_scheme]" value="custom" class="arm_temp_color_radio" data-type="profile">
                                </label>
                                <div class="arm_temp_color_options" id="arm_temp_color_options" style="<?php echo isset($options['color_scheme']) && $options['color_scheme'] == 'custom' ? 'display:block' : 'display:none'; ?>">
                                    <div class="arm_pdtemp_color_opts">
                                        <span class="arm_temp_form_label"><?php _e('Title Color', 'ARMember'); ?></span>
                                        <label class="arm_colorpicker_label arm_custom_colorpicker_label" style="background-color:<?php echo $options['title_color']; ?>">
                                            <input type="text" name="template_options[title_color]" id="arm_profile_title_color" class="arm_colorpicker" value="<?php echo $options['title_color']; ?>" />
                                        </label>
                                    </div>
                                    <div class="arm_pdtemp_color_opts">
                                        <span class="arm_temp_form_label"><?php _e('Sub Title Color', 'ARMember'); ?></span>
                                        <label class="arm_colorpicker_label arm_custom_colorpicker_label" style="background-color:<?php echo $options['subtitle_color']; ?>">
                                            <input type="text" name="template_options[subtitle_color]" id="arm_profile_subtitle_color" class="arm_colorpicker" value="<?php echo $options['subtitle_color']; ?>" />
                                        </label>
                                    </div>
                                    <div class="arm_pdtemp_color_opts">
                                        <span class="arm_temp_form_label"><?php _e('Border Color', 'ARMember'); ?></span>
                                        <label class="arm_colorpicker_label arm_custom_colorpicker_label" style="background-color:<?php echo $options['border_color']; ?>">
                                            <input type="text" name="template_options[border_color]" id="arm_profile_border_color" class="arm_colorpicker" value="<?php echo $options['border_color']; ?>" />
                                        </label>
                                    </div>
                                    <div class="arm_pdtemp_color_opts">
                                        <span class="arm_temp_form_label"><?php _e('Body Content Color', 'ARMember'); ?></span>
                                        <label class="arm_colorpicker_label arm_custom_colorpicker_label" style="background-color:<?php echo $options['content_font_color']; ?>">
                                            <input type="text" name="template_options[content_font_color]" id="arm_profile_content_color" class="arm_colorpicker" value="<?php echo $options['content_font_color']; ?>" />
                                        </label>
                                    </div>
                                    <?php do_action('arm_profile_color_options_outside',$options); ?>
                                </div>
                                <div class="arm_temp_color_option_footer">
                                    <button type="button" class="armemailaddbtn" id="arm_temp_color_option_apply_button"><?php _e('Apply','ARMember') ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>   <?php
                $user_id = get_current_user_id();
                $current_user_info = get_user_by('id', 1);
                $content = '';
                $content .= '<div class="arm_admin_profile_container">
                    <div class="arm_template_container arm_profile_container" id="arm_template_container_wrapper">';
                $content .= $arm_members_directory->arm_get_profile_editor_template($temp_slug,$profile_fields_data,$options,$profile_template,false,$arm_profile_before_content,$arm_profile_after_content);
                echo $content .= '</div></div>';
                ?> 
            </div>
            <div class="arm_profile_editor_right_div connectedSortable" id="answers">

                <div id="arm_accordion">
                    <ul>
                        <li class="arm_active_section">
                            <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Add New Block', 'ARMember'); ?>
                                <?php $gf_tooltip = __("You can add specific HTML before/after profile fields listing section", 'ARMember'); ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $gf_tooltip; ?>"></i>
                                <i></i></a>
                            <div id="one" class="arm_accordion default">
                                <div class="arm_accordion_inner_title"><?php _e('Before profile fields', 'ARMember'); ?></div>
                                <?php
                                $content = "";
                                $editor_id = "arm_before_profile_fields_content";
                                $arguments = array(
                                    'media_buttons' => false,
                                    'textarea_name' => 'arm_before_profile_fields_content',
                                    'textarea_rows' => 10,
                                    'editor_class' => 'arm_accordion_custom_block',
                                    'tinymce' => false,
                                );
                                wp_editor(stripslashes_deep($arm_profile_before_content), $editor_id, $arguments);
                                ?>
                                

                                <div class="arm_accordion_separator"></div>
                                <div class="arm_accordion_inner_title" style="margin-top: 20px;"><?php _e('After profile fields', 'ARMember'); ?></div>
                                <?php
                                $content = "";
                                $editor_id = "arm_after_profile_fields_content";
                                $arguments = array(
                                    'media_buttons' => false,
                                    'textarea_name' => 'arm_after_profile_fields_content',
                                    'textarea_rows' => 10,
                                    'editor_class' => 'arm_accordion_custom_block',
                                    'tinymce' => false,
                                );
                                wp_editor(stripslashes_deep($arm_profile_after_content), $editor_id, $arguments);
                                ?>
                               
                            </div>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Profile Fields', 'ARMember'); ?>
                                <?php $pf_tooltip = __("Select fields that you want to display in profile fields listing section.", 'ARMember'); ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $pf_tooltip; ?>"></i>
                                <i></i></a>
                            <div id="two" class="arm_accordion" data-id="arm_profile_fields_wrapper">
                                <div class="arm_profile_fields_dropdown">
                                    <input type="hidden" id="arm_profile_fields" value="" />
                                    <dl class="arm_selectbox column_level_dd" style="width:96%;">
                                        <dt style="width: 100%;"><span><?php _e('Select Field', 'ARMember'); ?></span><input type="text" style="display:none;" class="arm_autocomplete" readonly="readonly" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                        <dd>
                                            <ul data-id="arm_profile_fields" style="display: none;">
                                                <li data-label="<?php _e('Select Field', 'ARMember'); ?>" data-value=""><?php _e('Select Field', 'ARMember'); ?></li>
                                                <?php
                                                $dbProfileFields = $arm_members_directory->arm_template_profile_fields();
                                                foreach ($dbProfileFields as $fieldMetaKey => $fieldOpt) {
                                                    if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme', 'avatar','arm_captcha'))) {
                                                        continue;
                                                    }
                                                    $arm_is_deactive = '';
                                                    if(in_array($fieldMetaKey, $profile_fields_data['profile_fields'])){
                                                        $arm_is_deactive = ' class="arm_deactive" ';
                                                    }
                                                    ?>
                                                    <li data-code="<?php echo $fieldMetaKey; ?>" data-label="<?php echo stripslashes_deep($fieldOpt['label']); ?>" data-value="<?php echo stripslashes_deep($fieldOpt['label']); ?>" <?php echo $arm_is_deactive; ?>><?php echo stripslashes_deep($fieldOpt['label']); ?></li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="arm_accordion_separator"></div>
                                <div class="arm_accordion_separator"></div>
                                <div class="arm_accordion_inner_container" id="arm_profile_fields_inner_container">
                                    <?php
                                        foreach($profile_fields_data['profile_fields'] as $k => $pf ){
                                    ?>
                                        <div class="arm_add_profile_shortcode_row arm_user_custom_meta" id="arm_add_profile_shortcode_<?php echo $pf; ?>">
                                            <span class="arm_add_profile_variable_code arm_add_profile_user_meta" data-code="<?php echo $pf; ?>">
                                                <input type="text" value="<?php echo stripslashes_deep($profile_fields_data['label'][$pf]); ?>" id="arm_profile_field_input_<?php echo $pf; ?>" data-id="<?php echo $pf; ?>" name="profile_fields[<?php echo $pf; ?>]" class="arm_profile_field_input" />
                                            </span>
                                            <span class="arm_add_profile_field_icons">
                                                <span class="arm_profile_field_icon edit_field" id="arm_edit_field" data-code="<?php echo $pf; ?>" title="<?php _e('Edit Field Label', 'ARMember'); ?>"></span>
                                                <span class="arm_profile_field_icon delete_field" id="arm_delete_field" data-code="<?php echo $pf; ?>" title="<?php _e('Delete Field', 'ARMember'); ?>" onclick="showConfirmBoxCallback('<?php echo $pf; ?>');"></span>
                                                <span class="arm_profile_field_icon sort_field" id="arm_sort_field" data-code="<?php echo $pf; ?>" title="<?php _e('Move', 'ARMember'); ?>"></span>
                                            </span>
                                            <?php echo $arm_global_settings->arm_get_confirm_box($pf, __("Are you sure you want to delete this field?", 'ARMember'), 'arm_remove_profile_shortcode_row'); ?>
                                        </div>    
                                    <?php
                                        }
                                    ?>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Social Profile Fields', 'ARMember'); ?>
                                <?php $gf_tooltip = __("Select social profile fields that you want to display in profile header.", 'ARMember'); ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $gf_tooltip; ?>"></i>
                                <i></i></a>
                            <div id="three" class="arm_accordion"> 
                                <?php
                                $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();

                                foreach ($socialProfileFields as $SPFKey => $SPFLabel) {
                                    $checked = "";
                                    if( isset($options['arm_social_fields']) && in_array($SPFKey,$options['arm_social_fields'])){
                                        $checked = "checked='checked'";
                                    }
                                    ?>
                                    <div class='arm_social_profile_field_item'>
                                        <input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt' value='<?php echo $SPFKey; ?>' name='template_options[arm_social_fields][]' id='arm_spf_<?php echo $SPFKey; ?>_status' <?php echo $checked; ?> />
                                        <label for='arm_spf_<?php echo $SPFKey; ?>_status'><?php echo $SPFLabel; ?></label>
                                    </div>
                                    <?php
                                }
                                ?></div>
                        </li>
                        <?php if( $is_default_template < 1 ){
                            ?>
                        <li>
                            <a href="javascript:void(0)" class="arm_accordion_header"><?php _e('Membership Plans', 'ARMember'); ?>
                                <?php $gf_tooltip = __("Select membership plans, of which users, you want to display this profile template.", 'ARMember'); ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo $gf_tooltip; ?>"></i>
                                <i></i></a>
                            <div id="four" class="arm_accordion arm_admin_form">
                                <div class="arm_profile_membership_plan">
                                    <?php _e('Select Membership Plans', 'ARMember'); ?><br/>
                                    <select id="arm_temp_plans" class="arm_chosen_selectbox arm_template_plans_select" name="template_options[plans][]" data-placeholder="<?php _e('Select Plan(s)..', 'ARMember'); ?>" multiple="multiple">
                                        <?php if (!empty($subs_data)): ?>
                                            <?php foreach ($subs_data as $sd): ?>
                                                <option class="arm_message_selectbox_op" <?php echo (in_array($sd['arm_subscription_plan_id'],$subscription_plans)) ? 'selected="selected"' : ''; ?>  value="<?php echo $sd['arm_subscription_plan_id']; ?>"><?php echo stripslashes($sd['arm_subscription_plan_name']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>

                                </div>
                            </div>
                            </a>
                        </li>
                        <?php } ?>
                        <li>
                            <a href="javascript:void(0)" class="arm_accordion_header">
                                <?php _e('Other Settings', 'ARMember'); ?>
                                <?php $gf_tooltip = __('Select Other Settings.', 'ARMember'); ?>
                                
                                <i></i>
                            </a>
                            <div id="five" class="arm_accordion">
                                <div class="arm_profile_other_settings">
                                    <div class="arm_profile_setting_switch_div"><label for="arm_profile_display_admin_user"><?php _e('Display Administrator Users?', 'ARMember'); ?></label>
                                        <div class="armswitch arm_profile_setting_switch">
                                            <input type="checkbox" id="arm_profile_display_admin_user" value="1" class="armswitch_input" name="show_admin_users" <?php checked($display_admin_profile, 1); ?>/>
                                            <label for="arm_profile_display_admin_user" class="armswitch_label"></label>
                                        </div>
                                    </div>
                                    <div class="arm_profile_setting_switch_div"><label for="arm_hide_empty_profile_fields"><?php _e('Hide Empty Profile Fields?','ARMember'); ?></label>
                                        <div class="armswitch arm_profile_setting_switch">
                                            <input type="checkbox" id="arm_hide_empty_profile_fields" value="1" class="armswitch_input" name="template_options[hide_empty_profile_fields]" <?php checked($hide_empty_profile_fields, 1); ?>/>
                                            <label for="arm_hide_empty_profile_fields" class="armswitch_label"></label>
                                        </div>
                                    </div>
                                    <div class="arm_profile_setting_switch_div"><label for="arm_profile_display_badge"><?php _e('Display Member Badges?', 'ARMember'); ?></label>
                                        <div class="armswitch arm_profile_setting_switch">
                                            <input type="checkbox" id="arm_profile_display_badge" value="1" class="armswitch_input" name="template_options[show_badges]" <?php checked($display_member_badges, 1); ?>/>
                                            <label for="arm_profile_display_badge" class="armswitch_label"></label>
                                        </div>
                                    </div>
                                    <div class="arm_profile_setting_switch_div"><label for="arm_profile_display_joining_date"><?php _e('Display Joining Date?', 'ARMember'); ?></label>
                                        <div class="armswitch arm_profile_setting_switch">
                                            <input type="checkbox" id="arm_profile_display_joining_date" value="1" class="armswitch_input" name="template_options[show_joining]" <?php checked($display_joining_date, 1); ?>/>
                                            <label for="arm_profile_display_joining_date" class="armswitch_label"></label>
                                        </div>
                                    </div>
                                    <div class="arm_profile_setting_switch_div"><label for="arm_profile_display_cover_image"><?php _e('Default Cover Image?', 'ARMember'); ?></label>
                                        <div class="armswitch arm_profile_setting_switch">
                                            <input type="checkbox" id="arm_profile_display_cover_image" value="1" class="armswitch_input" name="template_options[default_cover_photo]" <?php checked($default_cover_photo, 1); ?>/>
                                            <label for="arm_profile_display_cover_image" class="armswitch_label"></label>
                                        </div>
                                        <?php
                                        $default_cover_url = isset($options['default_cover']) && $options['default_cover'] != '' ? $options['default_cover'] : MEMBERSHIP_IMAGES_URL.'/profile_default_cover.png';
                                        $show_remove_cover_photo = 0;
                                        if( $default_cover_photo == 1 &&  $default_cover_url != MEMBERSHIP_IMAGES_URL.'/profile_default_cover.png' ){
                                            $show_remove_cover_photo = 1;
                                        }
                                    ?>
                                        <div class="arm_profile_setting_switch_div" id="arm_profile_upload_buttons_div" style="<?php echo ($default_cover_photo != 1) ? 'display:none;' : ''; ?>">
                                            <div class="arm_accordion_separator"></div>
                                            <div class="arm_accordion_separator"></div>
                                            <div class="arm_accordion_separator"></div>
                                            <span class="arm_profile_upload_buttons_label"><?php _e('Default Cover Photo', 'ARMember'); ?></span>
                                            <div class="arm_default_cover_photo_wrapper" style="<?php echo ($show_remove_cover_photo) ? 'display:none' : 'display:inline-block'; ?>">
                                                <span><?php _e('Upload', 'ARMember') ?></span>
                                                <input type="file" data-update-meta='no' class="arm_accordion_file_upload_button armFileUpload" data-avatar-type="cover" id='armTempEditFileUpload' data-type="profile" />
                                            </div>
                                            <div class="arm_remove_default_cover_photo_wrapper" style="<?php echo ($show_remove_cover_photo) ? 'display:inline-block' : 'display:none'; ?>">
                                                <span><?Php _e('Remove','ARMember'); ?></span>
                                            </div>
                                            <input type='hidden' id='armTempEditFileUpload_hidden' class='armFileUpload_cover' name='template_options[default_cover]' value='<?php echo $default_cover_url; ?>' />
                                        </div>
                                    </div>
                                    <?php do_action('arm_profile_other_settings_outside',$options); ?>
                                </div>
                            </div>
                        </li>
                        <?php do_action('arm_profile_setting_section_outside',$options); ?>
                    </ul>
                </div>
            </div>
            <div class="arm_custom_css_popup_wrapper">
                <div class="arm_custom_css_popup_inner_wrapper">
                    <div class="popup_header">
                        <?php _e('Custom CSS', 'ARMember'); ?>
                        <span class="popup_close_btn arm_popup_close_btn arm_custom_css_popup_close_btn"></span>    
                    </div>
                    <div class="arm_custom_css_popup_container">
                        <textarea class="arm_codemirror_field" id="arm_codemirror_field" name="template_options[custom_css]" cols="10" rows="6" style="width: 500px;"><?php echo isset($options['custom_css']) ? $options['custom_css'] : ''; ?></textarea>
                    </div>
                    <div class="popup_content_btn popup_footer">
                        <button type="button" class="arm_custom_css_popup_footer_button" id="arm_custom_css_apply_button"><?php _e('Apply','ARMember'); ?></button>
                    </div>
                </div>
                <style type="text/css" id='arm_profile_template_custom_css'><?php echo isset($options['custom_css']) ? $options['custom_css'] : ''; ?></style>
            </div>
        </form>

    <?php 
        //wp_register_style( 'arm-jquery-ui-css', "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" );
        //wp_print_styles('arm-jquery-ui-css');
    ?>
        
        </div>

    <style id="arm_profile_runtime_style">



    </style>

</div>
<?php
if($enable_crop){ ?>
<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;">
    <div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>
    <div id="arm_crop_cover_div">
        <img id="arm_crop_cover_image" src="" style="max-width:100%;max-height: 100%;" />
    </div>
    <div class="arm_skip_cvr_crop_button_wrapper_admn">
        <button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" title="<?php _e('Crop', 'ARMember'); ?>" data-method="crop"><span class="armfa armfa-crop"></span></button>
        <button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" title="<?php _e('Clear', 'ARMember'); ?>" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>
        <button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" title="<?php _e('Zoom In', 'ARMember'); ?>"><span class="armfa armfa-search-plus"></span></button>
        <button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" title="<?php _e('Zoom Out', 'ARMember'); ?>"><span class="armfa armfa-search-minus"></span></button>
        <button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data-method="rotate" data-option="90" title="<?php _e('Rotate', 'ARMember'); ?>"><span class="armfa armfa-rotate-right"></span></button>
        <button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" title="<?php _e('Reset', 'ARMember'); ?>" data-method="reset"><span class="armfa armfa-refresh"></span></button>
        <button id="arm_skip_cvr_crop_nav_admn" class="arm_cvr_done_front"><?php _e('Done', 'ARMember'); ?></button>
    </div>

    <p class="arm_discription">(<?php _e('Use Cropper to set image and use mouse scroller for zoom image','ARMember' ); ?>.)</p>
</div>

<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper" style="display:none;">
    <div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>
    <div id="arm_crop_div">
        <img id="arm_crop_image" src="" style="max-width:100%;" />
    </div>
    <button class="arm_crop_button"><?php _e('crop','ARMember' ); ?></button>
    <p class="arm_discription">(<?php _e('Use Cropper to set image and use mouse scroller for zoom image','ARMember' ); ?>.)</p>
</div>
<?php 
}
?>
<script type="text/javascript">
    function armTempColorSchemes() {
        var tempColorSchemes = <?php echo json_encode($tempColorSchemes); ?>;
        return tempColorSchemes;
    }
    function armTempColorSchemes1() {
        var tempColorSchemes = <?php echo json_encode($tempColorSchemes1); ?>;
        return tempColorSchemes;
    }

    var DEFAULT_COVER = '<?php echo MEMBERSHIP_IMAGES_URL . "/profile_default_cover.png"; ?>';
    var EDIT_FIELD_LABEL = '<?php _e('Edit Field Label', 'ARMember'); ?>';
    var DELETE_FIELD = '<?php _e('Delete Field', 'ARMember'); ?>';
    var MOVE = '<?php _e('Move', 'ARMember'); ?>';
    var ARM_REMOVE_PROFILE_ROW_MSG = '<?php _e('Are you sure you want to delete this field?', 'ARMember'); ?>';
    var ARM_DELETE = '<?php _e('Delete', 'ARMember'); ?>';
    var ARM_CANCEL = '<?php _e('Cancel', 'ARMember'); ?>';
</script>