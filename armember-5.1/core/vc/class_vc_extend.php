<?php
if (!defined('WPINC')) {
    die;
}

class ARM_VCExtend {

    protected static $instance = null;
    var $is_membership_vdextend = 0;

    public function __construct() {
        add_action('init', array($this, 'ARM_arm_form'));
        add_action('init', array($this, 'ARM_arm_edit_profile'));
        add_action('init', array($this, 'ARM_arm_logout'));
        add_action('init', array($this, 'ARM_arm_social_login'));
        add_action('init', array($this, 'ARM_arm_setup'));
        add_action('init', array($this, 'ARM_arm_member_transaction'));
        add_action('init', array($this, 'ARM_arm_account_detail'));
        add_action('init', array($this, 'ARM_arm_close_account'));
        add_action('init', array($this, 'ARM_arm_membership'));
        add_action('init', array($this, 'ARM_arm_conditional_redirection'));
        add_action('init', array($this, 'ARM_arm_conditional_redirection_role'));
        add_action('init', array($this, 'ARM_arm_username'));
        add_action('init', array($this, 'ARM_arm_user_plan'));
        add_action('init', array($this, 'ARM_arm_displayname'));
        add_action('init', array($this, 'ARM_arm_firstname_lastname'));
        add_action('init', array($this, 'ARM_arm_avatar'));
        add_action('init', array($this, 'ARM_arm_usermeta'));
        add_action('init', array($this, 'ARM_arm_user_badge'));
        add_action('init', array($this, 'ARM_arm_user_planinfo'));
        add_action('init', array($this, 'ARM_init_all_shortcode'));
    }

    public function ARM_init_all_shortcode() {
        if (function_exists('vc_add_shortcode_param')) {
            vc_add_shortcode_param('ARM_arm_form_shortcode', array($this, 'ARM_arm_form_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_edit_profile_shortcode', array($this, 'ARM_arm_edit_profile_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_logout_shortcode', array($this, 'ARM_arm_logout_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_social_login_shortcode', array($this, 'ARM_arm_social_login_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_setup_shortcode', array($this, 'ARM_arm_setup_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_member_transaction_shortcode', array($this, 'ARM_arm_member_transaction_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_account_detail_shortcode', array($this, 'ARM_arm_account_detail_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_close_account_shortcode', array($this, 'ARM_arm_close_account_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_membership_shortcode', array($this,'ARM_arm_membership_html'), MEMBERSHIP_URL . '/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_conditional_redirection_shortcode',array($this,'ARM_arm_conditional_redirection_html'), MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_conditional_redirection_role_shortcode',array($this,'ARM_arm_conditional_redirection_role_html'), MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_username_shortcode',array($this,'ARM_arm_username_html'),MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_user_plan_shortcode',array($this,'ARM_arm_user_plan_html'),MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_displayname_shortcode',array($this,'ARM_arm_displayname_html'),MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_firstname_lastname_shortcode',array($this,'ARM_arm_firstname_lastname_html'),MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_avatar_shortcode',array($this,'ARM_arm_avatar_html'),MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_usermeta_shortcode',array($this,'ARM_arm_usermeta_html'),MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_user_badge_shortcode',array($this,'ARM_arm_user_badge_html'),MEMBERSHIP_URL.'/core/vc/arm_vc.js');
            vc_add_shortcode_param('ARM_arm_user_planinfo_shortcode',array($this,'ARM_arm_user_planinfo_html'),MEMBERSHIP_URL.'/core/vc/arm_vc.js');

        }
    }

    public function ARM_arm_form() {
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Form', 'ARMember'),
                'description' => '',
                'base' => 'arm_form',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'id',
                        'value' => '',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_form_shortcode',
                        'heading' => false,
                        'param_name' => 'logged_in_message',
                        'value' => '',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_form_shortcode',
                        'heading' => false,
                        'param_name' => 'assign_default_plan',
                        'value' => '',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_form_shortcode',
                        'heading' => false,
                        'param_name' => 'form_position',
                        'value' => 'center',
                        'description' => __('&nbsp;','ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => 'ARM_arm_form_shortcode',
                        'heading' => false,
                        'param_name' => 'popup',
                        'value' => false,
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'link_type',
                        'value' => 'link',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'link_title',
                        'value' => 'Click here to open Form',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'overlay',
                        'value' => '0.6',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'modal_bgcolor',
                        'value' => '#000000',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'popup_height',
                        'value' => 'auto',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'popup_width',
                        'value' => '700',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'link_css',
                        'value' => 'color: #000000;',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_form_shortcode",
                        'heading' => false,
                        'param_name' => 'link_hover_css',
                        'value' => 'color: #ffffff;',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                )
            ));
        }
    }

    public function ARM_arm_form_html($settings, $value) {
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_form]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Form', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php //_e('Member forms shortcode.', 'ARMember');        ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_form" style="width: 660px;">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Select a form to insert into page', 'ARMember'); ?></th>
                                                <td>
                                                    <?php
                                                    $arm_forms = $arm_member_forms->arm_get_all_member_forms('arm_form_id, arm_form_label, arm_form_type');
                                                    $armFormList = '';
                                                    if (!empty($arm_forms)) {
                                                        foreach ($arm_forms as $_form) {
                                                            $armFormList .= '<li class="arm_shortcode_form_id_li" data-form-type="'.$_form['arm_form_type'].'" data-label="' . strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')' . '" data-value="' . $_form['arm_form_id'] . '">' . strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')' . '</li>';
                                                        }
                                                    }
                                                    ?>
                                                    <input type="hidden" id="arm_form_select" class="wpb_vc_param_value" name="id" value="" onChange="arm_show_hide_logged_in_message(this.value)" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul class="arm_form_select" data-id="arm_form_select">
                                                                <li data-label="<?php _e('Select Form', 'ARMember'); ?>" data-value=""><?php _e('Select Form', 'ARMember'); ?></li>
                                                                <?php echo $armFormList; ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            <tr id="arm_member_form_default_free_plan" style="display:none;">
                                                <th><?php _e('Assign Default Plan','ARMember'); ?></th>
                                                <td>
                                                    <?php
                                                    $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
                                                    $arm_planlist = '';
                                                    if(!empty($all_plans)): 
                                                        foreach($all_plans as $plan):
                                                            if(!$arm_subscription_plans->isFreePlanExist($plan['arm_subscription_plan_id'])){ continue; } 
                                                            $arm_planlist .= '<li class="arm_shortcode_form_id_li" data-label="' . stripslashes($plan['arm_subscription_plan_name']) . '" data-value="' . $plan['arm_subscription_plan_id'] . '">' . stripslashes($plan['arm_subscription_plan_name']) . '</li>';
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                    <input type="hidden" id="assign_default_plan" class="wpb_vc_param_value" name="assign_default_plan" value="" />
                                                    <dl class="arm_selectbox column_level_dd" id="assign_default_plan_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul class="assign_default_plan" data-id="assign_default_plan">
                                                                <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Form', 'ARMember'); ?></li>
                                                                <?php echo $arm_planlist; ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            <tr id="arm_member_form_logged_in_message" style="display:none;">
                                                <th><?php _e('Logged in Message','ARMember'); ?></th>
                                                <td>
                                                    <input type="text" class="wpb_vc_param_value" name="logged_in_message" value="" id="logged_in_message" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('How you want to include this form into page?', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" name="popup" value="false" class="wpb_vc_param_value" id="arm_popup_hidden" />
                                                    <label class="form_popup_type_radio">
                                                        <input type="radio" name="arm_popup" value="false" checked="checked" id="arm_popup_false" onclick="arm_show_hide_popup();" class="arm_iradio" />
                                                        <?php _e('Internal', 'ARMember'); ?>
                                                    </label>
                                                    <label class="form_popup_type_radio">
                                                        <input type="radio" name="arm_popup" value="true" id="arm_popup_true" onclick="arm_show_hide_popup();" class="arm_iradio" />
                                                        <?php _e('External popup window', 'ARMember'); ?>
                                                    </label>
                                                    <div class="form_popup_options">
                                                        <div class="form_popup_options_row">
                                                            <span class="arm_opt_title"><?php _e('Link Type', 'ARMember'); ?>: </span>
                                                            <input type="hidden" id="arm_shortcode_form_link_type" class="wpb_vc_param_value" name="link_type" value="link" />
                                                            <dl class="arm_selectbox column_level_dd">
                                                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul data-id="arm_shortcode_form_link_type">
                                                                        <li data-label="<?php _e('Link', 'ARMember'); ?>" data-value="link"><?php _e('Link', 'ARMember'); ?></li>
                                                                        <li data-label="<?php _e('Button', 'ARMember'); ?>" data-value="button"><?php _e('Button', 'ARMember'); ?></li>
                                                                        <li data-label="<?php _e('On Load', 'ARMember'); ?>" data-value="onload"><?php _e('On Load', 'ARMember'); ?></li>
                                                                    </ul>
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                        <div class="form_popup_options_row">
                                                            <span class="arm_opt_title arm_shortcode_form_link_opts"><?php _e('Link Text', 'ARMember'); ?>: </span>
                                                            <span class="arm_opt_title arm_shortcode_form_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?>: </span>
                                                            <input type="text" class="wpb_vc_param_value" name="link_title" value="<?php _e('Click here to open Form', 'ARMember'); ?>" id="arm_link_title" />
                                                        </div>
                                                        <div class="form_popup_options_row arm_form_background_overlay">
                                                            <span class="arm_opt_title"><?php _e('Background Overlay', 'ARMember'); ?>: </span>
                                                            <div>
                                                                <input type="hidden" id="arm_overlay_select" name="overlay" value="0.6" class="wpb_vc_param_value">
                                                                <dl class="arm_selectbox column_level_dd">
                                                                    <dt style="width: 80px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                    <dd>
                                                                        <ul data-id="arm_overlay_select">
                                                                            <li data-label="0 (<?php _e('None', 'ARMember'); ?>)" data-value="0">0 (<?php _e('None', 'ARMember'); ?>)</li>
                                                                            <?php for ($i = 1; $i < 11; $i++): ?>

                                                                                <li data-label="<?php echo $i * 10; ?>" data-value="<?php echo $i / 10; ?>"><?php echo $i * 10; ?></li>
                                                                            <?php endfor; ?>
                                                                        </ul>
                                                                    </dd>
                                                                </dl>
                                                            </div>
                                                            <div><input id="arm_vc_form_modal_bgcolor" type="text" name="modal_bgcolor" class="arm_colorpicker arm_form_modal_bgcolor wpb_vc_param_value" value="#000000" /><em>&nbsp;&nbsp;(<?php _e('Background Color', 'ARMember'); ?>)</em></div>
                                                        </div>
                                                        <div class="armclear"></div>
                                                        <div class="form_popup_options_row arm_form_popup_size">
                                                            <span class="arm_opt_title"><?php _e('Size', 'ARMember'); ?>: </span>
                                                            <div><input class="wpb_vc_param_value" type="text" name="popup_height" id="arm_popup_height" value="" /><br/><?php _e('Height', 'ARMember'); ?></div>
                                                            <span class="popup_height_suffinx">px</span>
                                                            <div><input class="wpb_vc_param_value" type="text" name="popup_width" id="arm_popup_width" value="" /><br/><?php _e('Width', 'ARMember'); ?></div>
                                                            <span class="popup_width_suffinx">px</span>
                                                        </div>
                                                        <div class="form_popup_options_row">
                                                            <span class="arm_opt_title arm_shortcode_form_link_opts" style="vertical-align: top;"><?php _e('Link CSS', 'ARMember'); ?>: </span>
                                                            <span class="arm_opt_title arm_shortcode_form_button_opts arm_hidden" style="vertical-align: top;"><?php _e('Button CSS', 'ARMember'); ?>: </span>
                                                            <textarea class="arm_popup_textarea wpb_vc_param_value" name="link_css" id="arm_link_css" rows="3"></textarea>
                                                        </div>
                                                        <div class="form_popup_options_row">
                                                            <span class="arm_opt_title arm_shortcode_form_link_opts" style="vertical-align: top;"><?php _e('Link Hover CSS', 'ARMember'); ?>: </span>
                                                            <span class="arm_opt_title arm_shortcode_form_button_opts arm_hidden" style="vertical-align: top;"><?php _e('Button Hover CSS', 'ARMember'); ?>: </span>
                                                            <textarea class="arm_popup_textarea wpb_vc_param_value" name="link_hover_css" id="arm_link_hover_css" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr id="arm_form_position_wrapper">
                                                <th><?php _e('Form Position','ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" name="form_position" value="center" class="wpb_vc_param_value" id="arm_position_hidden" />
                                                    <label class="form_position_type_radio">
                                                        <input type="radio" name="arm_form_position" value="left" id="arm_position_left" onclick="arm_position_input()" class="arm_iradio" />
                                                        <?php _e('Left','ARMember'); ?>
                                                    </label>
                                                    <label class="form_position_type_radio">
                                                        <input type="radio" name="arm_form_position" value="center" checked="checked" id="arm_position_center" onclick="arm_position_input()" class="arm_iradio" />
                                                        <?php _e('Center','ARMember'); ?>
                                                    </label>
                                                    <label class="form_position_type_radio">
                                                        <input type="radio" name="arm_form_position" value="right" id="arm_position_right" onclick="arm_position_input()" class="arm_iradio" />
                                                        <?php _e('Right','ARMember'); ?>
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                                <div class="armclear"></div>
                            </div>
                        </div>
                        <div class="armclear"></div>
                    </div>

                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_edit_profile() {
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Edit Profile', 'ARMember'),
                'description' => '',
                //'base' => 'arm_edit_profile',
                'base' => 'arm_profile_detail',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        'type' => "ARM_arm_edit_profile_shortcode",
                        'heading' => false,
                        'param_name' => 'form_position',
                        'value' => __('Form Position', 'ARMember'),
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_edit_profile_shortcode',
                        'heading' => false,
                        'param_name' => 'form_id',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }

    public function ARM_arm_edit_profile_html($settings, $value) {
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans,$arm_social_feature;

        $arm_forms = $arm_member_forms->arm_get_all_member_forms('arm_form_id, arm_form_label, arm_form_type');

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <input type="hidden" id="ajax_url_hidden" value="<?php echo admin_url('admin-ajax.php'); ?>" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_logout]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Edit Profile', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Logout Shortcode.', 'ARMember');        ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_edit_profile">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Select Form', 'ARMember'); ?></th>
                                                <td>
                                                    <div>
                                                        <input type="hidden" id="arm_edit_profile_form" name="form_id" value="" class="wpb_vc_param_value" <?php if ($arm_social_feature->isSocialFeature): ?> onChange="arm_get_social_fields(this.value);" <?php endif; ?> >
                                                        <dl class="arm_selectbox column_level_dd">
                                                            <dt>
                                                            <span><?php _e('Select Form', 'ARMember'); ?></span>
                                                            <input type="text" style="display:none;" value="<?php _e('Select Form', 'ARMember'); ?>" class="arm_autocomplete"/>
                                                            <i class="armfa armfa-caret-down armfa-lg"></i>
                                                            </dt>
                                                            <dd>
                                                                <ul data-id="arm_edit_profile_form">
                                                                    <li data-label="<?php _e('Select Form', 'ARMember'); ?>" data-value="">
                                                                        <?php _e('Select Form', 'ARMember'); ?>
                                                                    </li>
                                                                    <?php if (!empty($arm_forms)): ?>
                                                                        <?php
                                                                        foreach ($arm_forms as $_form):
                                                                            if ($_form['arm_form_type'] == 'edit_profile') {
                                                                                $formTitle = strip_tags(stripslashes($_form['arm_form_label'])) . ' &nbsp;(ID: ' . $_form['arm_form_id'] . ')';
                                                                                ?>
                                                                                <li class="arm_shortcode_form_id_li_edit_profile <?php echo $_form['arm_form_type']; ?>" data-label="<?php echo $formTitle; ?>" data-value="<?php echo $_form['arm_form_id']; ?>"><?php echo $formTitle; ?></li>
                                                                                <?php
                                                                            }
                                                                        endforeach;
                                                                        ?>
            <?php endif; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Form Position', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" name="form_position" class="wpb_vc_param_value" value="center" id="arm_edit_profile_position" />
                                                    <label class="form_position_type_radio">
                                                        <input type="radio" name="arm_edit_profile_position" value="left" id="arm_edit_profile_form_left" onclick="arm_edit_form_position_input()" class="arm_iradio" />
                                                        <?php _e('Left','ARMember'); ?>
                                                    </label>
                                                    <label class="form_position_type_radio">
                                                        <input type="radio" name="arm_edit_profile_position" value="center" checked="checked" id="arm_edit_profile_form_center" onclick="arm_edit_form_position_input()" class="arm_iradio" />
                                                        <?php _e('Center','ARMember'); ?>
                                                    </label>
                                                    <label class="form_position_type_radio">
                                                        <input type="radio" name="arm_edit_profile_position" value="right" id="arm_edit_profile_form_right" onclick="arm_edit_form_position_input()" class="arm_iradio" />
                                                        <?php _e('Right','ARMember'); ?>
                                                    </label>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_logout() {
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Logout', 'ARMember'),
                'description' => '',
                'base' => 'arm_logout',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_logout_shortcode",
                        'heading' => false,
                        'param_name' => 'label',
                        'value' => __('Logout', 'ARMember'),
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_logout_shortcode",
                        'heading' => false,
                        'param_name' => 'type',
                        'value' => 'link',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_logout_shortcode",
                        'heading' => false,
                        'param_name' => 'user_info',
                        'value' => 'true',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => 'ARM_arm_logout_shortcode',
                        'heading' => false,
                        'param_name' => 'redirect_to',
                        'value' => ARM_HOME_URL,
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_logout_shortcode",
                        'heading' => false,
                        'param_name' => 'link_css',
                        'value' => 'color: #000000;',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_logout_shortcode",
                        'heading' => false,
                        'param_name' => 'link_hover_css',
                        'value' => 'color: #ffffff;',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                )
            ));
        }
    }

    public function ARM_arm_logout_html($settings, $value) {
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_logout]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Logout', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Logout Shortcode.', 'ARMember');        ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_logout">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Link Type', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" id="arm_shortcode_logout_link_type" class="wpb_vc_param_value" name="type" value="link" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_shortcode_logout_link_type">
                                                                <li data-label="<?php _e('Link', 'ARMember'); ?>" data-value="link"><?php _e('Link', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('Button', 'ARMember'); ?>" data-value="button"><?php _e('Button', 'ARMember'); ?></li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <span class="arm_shortcode_logout_link_opts"><?php _e('Link Text', 'ARMember'); ?></span>
                                                    <span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button Text', 'ARMember'); ?></span>
                                                </th>
                                                <td><input type="text" name="label" class="wpb_vc_param_value" id="arm_logout_label" value="<?php _e('Logout', 'ARMember'); ?>"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Display User Info?', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" name="user_info" value="true" class="wpb_vc_param_value" id="arm_user_info_hidden" />
                                                    <label>
                                                        <input type="radio" name="arm_user_info" value="true" checked="checked" id="arm_user_info_true" onclick="arm_user_info_action();" class="arm_iradio" />
                                                        <span><?php _e('Yes', 'ARMember'); ?></span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="arm_user_info" value="false"  id="arm_user_info_false" onclick="arm_user_info_action();" class="arm_iradio" />
                                                        <span><?php _e('No', 'ARMember'); ?></span>
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Redirect After Logout', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="text" class="wpb_vc_param_value" name="redirect_to" value="<?php echo ARM_HOME_URL; ?>" id="arm_redirect_to" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <span class="arm_shortcode_logout_link_opts"><?php _e('Link CSS', 'ARMember'); ?></span>
                                                    <span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button CSS', 'ARMember'); ?></span>
                                                </th>
                                                <td>
                                                    <textarea class="arm_popup_textarea wpb_vc_param_value" id="arm_logout_link_css" name="link_css" rows="3"></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <span class="arm_shortcode_logout_link_opts"><?php _e('Link Hover CSS', 'ARMember'); ?></span>
                                                    <span class="arm_shortcode_logout_button_opts arm_hidden"><?php _e('Button Hover CSS', 'ARMember'); ?></span>
                                                </th>
                                                <td>
                                                    <textarea class="arm_popup_textarea wpb_vc_param_value" id="arm_logout_link_hover_css" name="link_hover_css" rows="3"></textarea>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>	
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_social_login() {
        global $arm_version, $ARMember, $arm_social_feature;
        $social_options = $arm_social_feature->arm_get_active_social_options();
        if(($arm_social_feature->isSocialLoginFeature) == 1 && !empty($social_options)){
            $networks = $arm_social_feature->arm_get_social_settings();
            $social_networks = $networks['options'];
            $icon = $social_networks['facebook']['icon'];
            $icon_list = $arm_social_feature->arm_get_social_network_icons('facebook');
            $icon_url = isset($icon_list[$icon]) ? $icon_list[$icon] : '';
            if (function_exists('vc_map')) {
                vc_map(array(
                    'name' => __('ARMember Social Login', 'ARMember'),
                    'description' => '',
                    'base' => 'arm_social_login',
                    'category' => __('ARMember', 'ARMember'),
                    'class' => '',
                    'controls' => 'full',
                    'icon' => 'arm_vc_icon',
                    'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                    'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                    'params' => array(
                        array(
                            'type' => "ARM_arm_social_login_shortcode",
                            'heading' => false,
                            'param_name' => 'network',
                            'value' => '',
                            'description' => '&nbsp;',
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'ARM_arm_social_login_shortcode',
                            'heading' => false,
                            'param_name' => 'icon',
                            'value' => $icon_url,
                            'description' => '&nbsp;',
                            'admin_label' => true
                        )
                    )
                ));
            }
        }
    }

    public function ARM_arm_social_login_html($settings, $value) {
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans, $arm_social_feature;

        if ($arm_social_feature->isSocialLoginFeature) {
            echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

            if ($this->is_membership_vdextend == 0) {
                $this->is_membership_vdextend = 1;
                ?>
                <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
                <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />

                <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                    <div class="arm_tinymce_content_block accordion_menu">
                        <!-- *********************[arm_social_login]********************* -->
                        <div class="arm_tinymce_shortcode_content">
                            <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Social Login', 'ARMember'); ?></div>
                            <div class="arm_shortcode_detail_wrapper">
                                <div class="arm_shortcode_detail_container">
                                    <div class="arm_shortcode_description"><?php //_e('Social Login Buttons', 'ARMember'); ?></div>
                                    <div class="arm_shortcode_generator_form arm_generator_arm_social_login" style="width: 660px;">
                                        <form onsubmit="return false;">
                                            <table class="arm_shortcode_option_table">
                                                <tr>
                                                    <th>
                                                        <span class="arm_social_login_networks"><?php _e('Network Type', 'ARMember'); ?></span>
                                                    </th>
                                                    <td>
                                                        <?php
                                                        $networks = $arm_social_feature->arm_get_social_settings();
                                                        $active_networks = $arm_social_feature->arm_get_active_social_options();
                                                        
                                                        $social_networks = $networks['options'];
                                                        ?>
                                                        <input type="hidden" id="arm_shortcode_social_networks" class="wpb_vc_param_value" name="network" value="" onChange="arm_social_networks_icon_list(this.value);" />
                                                        <dl class="arm_selectbox column_level_dd">
                                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                            <dd>
                                                                <ul data-id="arm_shortcode_social_networks">
                                                                    <li data-label="<?php _e('Select Network Type', 'ARMember'); ?>" class="arm_social_login_network" data-icon="" data-value=""><?php _e('Select Network Type', 'ARMember'); ?></li>
                                                                    <?php
                                                                        
                                                                        foreach($social_networks as $key => $value ){
                                                                            if(!array_key_exists($key, $active_networks)){
                                                                                continue;
                                                                            }
                                                                            $icon = $value['icon'];
                                                                            $label = $value['label'];
                                                                            $value = $key;
                                                                    ?>
                                                                    <li data-label="<?php echo $label; ?>" class="arm_social_login_network" data-icon="<?php echo $icon; ?>" data-value="<?php echo $value; ?>"><?php echo $label; ?></li>
                                                                    <?php
                                                                        }
                                                                    ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        <span class="arm_social_login_network_icon"><?php _e('Network icon', 'ARMember'); ?></span>
                                                    </th>
                                                    <td>
                                                        <input type="hidden" name="icon" id="arm_social_network_icon_hidden" class="wpb_vc_param_value" value="" />
                                                        <?php
                                                            foreach($social_networks as $key => $value ){
                                                                $current_icon = $value['icon'];
                                                                $icon_list = $arm_social_feature->arm_get_social_network_icons($key);
                                                        ?>
                                                        <div class="arm_social_network_icons" id="social_network_<?php echo $key; ?>_icon">
                                                        <?php
                                                                foreach( $icon_list as $icon => $icon_url_value ){
                                                                    $icon_value = $icon_url_value;
                                                        ?>
                                                            <label>
                                                                <input type="radio" name="arm_social_icon" onclick="arm_set_social_network_icon();" value="<?php echo $icon_value; ?>" data-key="<?php echo $icon; ?>" class="arm_social_network_icons arm_iradio" /><span><img src="<?php echo $icon_value; ?>" /></span>
                                                            </label>
                                                        <?php
                                                                }
                                                        ?>
                                                            </div>
                                                        <?php
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>	
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    }

    public function ARM_arm_setup() {
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Membership Setup Wizard', 'ARMember'),
                'description' => '',
                'base' => 'arm_setup',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_setup_shortcode",
                        'heading' => false,
                        'param_name' => 'id',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_setup_shortcode",
                        'heading' => false,
                        'param_name' => 'hide_title',
                        'value' => false,
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_setup_shortcode",
                        'heading' => false,
                        'param_name' => 'hide_plans',
                        'value' => 0,
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_setup_shortcode",
                        'heading' => false,
                        'param_name' => 'subscription_plan',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => "ARM_arm_setup_shortcode",
                        'heading' => false,
                        'param_name' => 'popup',
                        'value' => 'false',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_setup_shortcode',
                        'heading' => false,
                        'param_name' => 'link_type',
                        'value' => 'link',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_setup_shortcode",
                        'heading' => false,
                        'param_name' => 'link_title',
                        'value' => 'Click here to open Form test',
                        'description' => __('&nbsp;', 'ARMember'),
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_setup_shortcode',
                        'heading' => false,
                        'param_name' => 'overlay',
                        'value' => '0.6',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_setup_shortcode',
                        'heading' => false,
                        'param_name' => 'modal_bgcolor',
                        'value' => '#000000',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_setup_shortcode',
                        'heading' => false,
                        'param_name' => 'popup_height',
                        'value' => 'auto',
                        'description' => '&nbsp;',
                        'admin_label' => true,
                    ),
                    array(
                        'type' => 'ARM_arm_setup_shortcode',
                        'heading' => false,
                        'param_name' => 'popup_width',
                        'value' => '800',
                        'description' => '&nbsp;',
                        'admin_label' => true,
                    ),
                    array(
                        'type' => 'ARM_arm_setup_shortcode',
                        'heading' => false,
                        'param_name' => 'link_css',
                        'value' => 'color:#000000;',
                        'description' => '&nbsp;',
                        'admin_label' => true,
                    ),
                    array(
                        'type' => 'ARM_arm_setup_shortcode',
                        'heading' => false,
                        'param_name' => 'link_hover_css',
                        'value' => 'color:#ffffff;',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }

    public function ARM_arm_setup_html($settings, $value) {
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;
        
        $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `" . $ARMember->tbl_arm_membership_setup . "` ");
        if ($settings['param_name'] == 'id') {
            $value = (!empty($value) ? $value : (!empty($setups[0]) ? $setups[0]->arm_setup_id : ''));
        }
        
        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_setup]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Membership Setup Wizard', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Membership Setup Wizard Shortcode.', 'ARMember'); ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_setup" style="width:660px;">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Select Setup', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" id="arm_subscription_id_select" class="wpb_vc_param_value" name="id" value="" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_subscription_id_select">
                                                                <!--<li data-label="<?php _e('Select Setup', 'ARMember'); ?>" data-value=""><?php _e('Select Action Type', 'ARMember'); ?></li>-->
                                                                <?php if (!empty($setups)): ?>
                                                                    <?php foreach ($setups as $ms): ?>
                                                                        <li data-label="<?php echo stripslashes($ms->arm_setup_name); ?>" data-value="<?php echo $ms->arm_setup_id; ?>"><?php echo stripslashes($ms->arm_setup_name); ?></li>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <th><?php _e('Hide Setup Title?', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" name="hide_title" value="false" id="arm_subscription_show_hide_title_hidden" class="wpb_vc_param_value" />
                                                    <label>
                                                        <input type="radio" name="arm_subscription_hide_title" value="true" id="arm_subscription_hide_title_true" onclick="arm_subscription_show_hide_title();" class="arm_iradio" />
                                                        <span><?php _e('Yes', 'ARMember'); ?></span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="arm_subscription_hide_title" value="false" id="arm_subscription_hide_title_false" onclick="arm_subscription_show_hide_title();" class="arm_iradio" />
                                                        <span><?php _e('No', 'ARMember'); ?></span>
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr>
                                    <th><?php _e('Default Selected Plan', 'ARMember'); ?></th>
                                    <td>
                                        <input type="text" name="subscription_plan" value="" id="subscription_plan_input" class="wpb_vc_param_value" >
                                        <div><em><?php _e('Please enter plan id', 'ARMember'); ?></em></div>
                                    </td>
                                </tr>
                                            <tr>
                                    <th><?php _e('Hide Plan Selection Area', 'ARMember'); ?></th>
                                    <td>
                                        <input type="hidden" name="hide_plans" value="0"  class="wpb_vc_param_value hide_plans">
                                        <input type="checkbox" name="hide_plans_checkbox" onchange="arm_change_hide_plan_settigs()" class="wpb_vc_param_value hide_plans_checkbox">
                                        
                                        
                                    </td>
                                </tr>
                                            
                                            <tr>
                                                <th><?php _e('How you want to include this form into page?', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" name="popup" value=""  id="arm_subscription_display_form_type_hidden" class="wpb_vc_param_value"/>
                                                    <label>
                                                        <input type="radio" name="arm_subscription_display_type" id="arm_subscription_display_type_internal" class="arm_iradio" value="false" onclick="arm_subscription_setup_display_type();" />
                                                        <span><?php _e('Internal', 'ARMember'); ?></span>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="arm_subscription_display_type" id="arm_subscription_display_type_external" class="arm_iradio" value="true" onclick="arm_subscription_setup_display_type();" />
                                                        <span><?php _e('External', 'ARMember'); ?></span>
                                                    </label>
                                                    
                                                    <div class="form_popup_options">
                                                        <div class="form_popup_options_row">
                                                            <span class="arm_opt_title"><?php _e('Link Type', 'ARMember'); ?></span>
                                                            <input type="hidden" id="arm_subscription_link_type" class="wpb_vc_param_value" name="link_type" value="link" />
                                                            <dl class="arm_selectbox column_level_dd">
                                                                <dt><span><?php _e('Link', 'ARMember'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul data-id="arm_subscription_link_type">
                                                                        <li data-label="<?php echo _e('Link', 'ARMember'); ?>" data-value="link"><?php echo _e('Link', 'ARMember'); ?></li>
                                                                        <li data-label="<?php echo _e('Button', 'ARMember'); ?>" data-value="button"><?php echo _e('Button', 'ARMember'); ?></li>
                                                                    </ul>
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                        <div class="form_popup_options_row">
                                                            <span class="arm_opt_title arm_shortcode_setup_link_opts"><?php _e('Link Text', 'ARMember'); ?></span>
                                                            <span class="arm_opt_title arm_hidden arm_shortcode_setup_button_opts"><?php _e('Button Text', 'ARMember'); ?></span>
                                                            <input type="text" class="wpb_vc_param_value" name="link_title" value="<?php _e('Click Here to Open form', 'ARMember'); ?>" id="arm_setup_link_text_id" />                                                        
                                                        </div>
                                                        <div class="form_popup_options_row arm_setup_background_overlay">
                                                            <span class="arm_opt_title"><?php _e('Background Overlay', 'ARMember'); ?></span>
                                                            <div>
                                                                <input type="hidden" id="arm_overlay_select" name="overlay" value="0.6" class="wpb_vc_param_value" />
                                                                <dl class="arm_selectbox column_level_dd">
                                                                    <dt style="width:80px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class=" armfa armfa-caret-down armfa-lg"></i></dt>
                                                                    <dd>
                                                                        <ul data-id="arm_overlay_select">
                                                                            <li data-label="0 (<?php _e('None', 'ARMember'); ?>)" data-value="0">0 (<?php _e('None', 'ARMember'); ?>)</li>
                                                                            <?php for ($i = 1; $i < 11; $i++): ?>

                                                                                <li data-label="<?php echo $i * 10; ?>" data-value="<?php echo $i / 10; ?>"><?php echo $i * 10; ?></li>
                                                                            <?php endfor; ?>
                                                                        </ul>
                                                                    </dd>
                                                                </dl>
                                                            </div>
                                                            <div>
                                                                <input id="arm_vc_setup_modal_bgcolor" type="text" name="modal_bgcolor" class="arm_colorpicker modal_bgcolor wpb_vc_param_value" value="#000000" /><em>&nbsp;&nbsp;(<?php _e('Background Color', 'ARMember'); ?>)</em>
                                                            </div>
                                                        </div>
                                                        <div class="armclear"></div>
                                                        <div class="form_popup_options_row arm_setup_popup_size">
                                                            <span class="arm_opt_title"><?php _e('Size', 'ARMember'); ?>: </span>
                                                            <div><input class="wpb_vc_param_value" type="text" name="popup_height" id="arm_setup_popup_height" value="" /><br/><?php _e('Height', 'ARMember'); ?></div>
                                                            <span class="popup_height_suffinx">px</span>
                                                            <div><input class="wpb_vc_param_value" type="text" name="popup_width" id="arm_setup_popup_width" value="" /><br/><?php _e('Width', 'ARMember'); ?></div>
                                                            <span class="popup_width_suffinx">px</span>
                                                        </div>
                                                        <div class="form_popup_options_row">
                                                            <span class="arm_opt_title arm_shortcode_setup_link_opts" style="vertical-align: top;"><?php _e('Link CSS', 'ARMember'); ?>: </span>
                                                            <span class="arm_opt_title arm_shortcode_setup_button_opts arm_hidden" style="vertical-align: top;"><?php _e('Button CSS', 'ARMember'); ?>: </span>
                                                            <textarea class="arm_popup_textarea wpb_vc_param_value" name="link_css" id="arm_link_css" rows="3"></textarea>
                                                        </div>
                                                        <div class="form_popup_options_row">
                                                            <span class="arm_opt_title arm_shortcode_setup_link_opts" style="vertical-align: top;"><?php _e('Link Hover CSS', 'ARMember'); ?>: </span>
                                                            <span class="arm_opt_title arm_shortcode_setup_button_opts arm_hidden" style="vertical-align: top;"><?php _e('Button Hover CSS', 'ARMember'); ?>: </span>
                                                            <textarea class="arm_popup_textarea wpb_vc_param_value" name="link_hover_css" id="arm_link_hover_css" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_member_transaction() {
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Payment Transaction', 'ARMember'),
                'description' => '',
                'base' => 'arm_member_transaction',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_member_transaction_shortcode",
                        'heading' => false,
                        'param_name' => 'label',
                        'value' => 'transaction_id,invoice_id,plan,payment_gateway,payment_type,transaction_status,amount,used_coupon_code,used_coupon_discount,payment_date,',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_member_transaction_shortcode',
                        'heading' => false,
                        'param_name' => 'value',
                        'value' => 'Transaction ID,Invoice ID,Plan,Payment Gateway,Payment Type,Transaction Status,Amount,Used coupon Code,Used coupon Discount,Payment Date,',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_member_transaction_shortcode",
                        'heading' => false,
                        'param_name' => 'title',
                        'value' => __('Transactions', 'ARMember'),
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_member_transaction_shortcode",
                        'heading' => false,
                        'param_name' => 'display_invoice_button',
                        'value' => 'true',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_member_transaction_shortcode",
                        'heading' => false,
                        'param_name' => 'view_invoice_text',
                        'value' => __('View Invoice','ARMember'),
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_member_transaction_shortcode",
                        'heading' => false,
                        'param_name' => 'view_invoice_css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_member_transaction_shortcode",
                        'heading' => false,
                        'param_name' => 'view_invoice_hover_css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),

                    array(
                        'type' => 'ARM_arm_member_transaction_shortcode',
                        'heading' => false,
                        'param_name' => 'per_page',
                        'value' => '5',
                        'description' => "&nbsp",
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_member_transaction_shortcode",
                        'heading' => false,
                        'param_name' => 'message_no_record',
                        'value' => __('There is no any Transactions found', 'ARMember'),
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                )
            ));
        }
    }

    public function ARM_arm_member_transaction_html($settings, $value) {
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <script type="text/javascript">
                __TRANSACTION_FIELD_VALUES = "Transaction ID,Plan,Payment Gateway,Payment Type,Transaction Status,Amount,Used coupon Code,Used coupon Discount,Payment Date";
            </script>
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_member_transaction]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Transaction', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Display user\'s activities', 'ARMember');        ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_member_transaction">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Transaction History','ARMember'); ?></th>
                                                <td>
                                                    <input type='hidden' name='label' class='wpb_vc_param_value' id='arm_transaction_label_hidden' value='' />
                                                    <input type='hidden' name='value' class='wpb_vc_param_value' id='arm_transaction_value_hidden' value='' />
                                                    <ul class="arm_member_transaction_fields">
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="transaction_id" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Transaction ID','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="invoice_id" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Invoice ID','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="plan" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Plan','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="payment_gateway" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Payment Gateway','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="payment_type" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Payment Type','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="transaction_status" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Transaction Status','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="amount" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Amount','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="used_coupon_code" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Used Coupon Code','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="used_coupon_discount" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Used Coupon Discount','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_transaction_field_list">
                                                            <label class="arm_member_transaction_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_transaction_fields arm_member_transaction_field_input" name="arm_transaction_fields[]" value="payment_date" checked="checked" onchange="arm_select_transaction_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_transaction_fields" onkeyup="arm_select_transaction_fields()" name="value[]" value="<?php _e('Payment Date','ARMember'); ?>" />
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>




                                            <tr>
                                                <th><?php _e('Display View Invoice Button?','ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" name="display_invoice_button" id="display_invoice_button" value="true" class="wpb_vc_param_value">
                                                    <label class="view_invoice_radio">
                                                        <input type="radio" name="display_invoice_button_radio" id="display_invoice_button_radio_false" value="false" class="arm_iradio arm_shortcode_subscription_opt" onclick="arm_display_invoice();" />
                                                        <?php _e('No', 'ARMember'); ?>
                                                    </label>
                                                    <label class="view_invoice_radio">
                                                        <input type="radio" name="display_invoice_button_radio" id="display_invoice_button_radio_true" value="true" class="arm_iradio arm_shortcode_subscription_opt" checked="checked" onclick="arm_display_invoice();" />
                                                        <?php _e('Yes','ARMember'); ?>
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr class="view_invoice_btn_options">
                                                <th><?php _e('View Invoice Text','ARMember'); ?></th>
                                                <td><input type="text" name="view_invoice_text" id="view_invoice_text_input" value="<?php _e('View Invoice','ARMember'); ?>" class="wpb_vc_param_value" /></td>
                                            </tr>
                                          
                                            <tr class="view_invoice_btn_options">
                                                <th><?php _e('Button CSS','ARMember'); ?></th>
                                                <td>
                                                    <textarea class="arm_popup_textarea wpb_vc_param_value" name="view_invoice_css" id="view_invoice_css_input" rows="3"></textarea>
                                                    <br/>
                                                    <em>e.g. color: #ffffff;</em>
                                                </td>
                                            </tr>

                                            <tr class="view_invoice_btn_options">
                                                <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                                <td>
                                                    <textarea class="arm_popup_textarea wpb_vc_param_value" name="view_invoice_hover_css" id="view_invoice_hover_css_input" rows="3"></textarea>
                                                    <br/>
                                                    <em>e.g. color: #ffffff;</em>
                                                </td>
                                            </tr>


                                            <tr>
                                                <th><?php _e('Title', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="text" name="title"  id="arm_transaction_title"  value="<?php _e('Transactions', 'ARMember'); ?>" class="wpb_vc_param_value"/>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Records per Page','ARMember'); ?></th>
                                                <td>
                                                    <input type="text" name="per_page" id="arm_transaction_per_page_record" value="" class="wpb_vc_param_value" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('No Records Message', 'ARMember'); ?></th>
                                                <td>
                                                    <input type="text" name="message_no_record"  id="arm_transaction_message_no_record" value="<?php _e('There is no any Transactions found', 'ARMember'); ?>" class="wpb_vc_param_value"/>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>	
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_account_detail() {
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember My Profile', 'ARMember'),
                'description' => '',
                'base' => 'arm_account_detail',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    
                    array(
                        "type" => "ARM_arm_account_detail_shortcode",
                        'heading' => false,
                        'param_name' => 'label',
                        'value' => 'first_name,last_name,display_name,user_login,user_email',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_account_detail_shortcode',
                        'heading' => false,
                        'param_name' => 'value',
                        'value' => 'First Name,Last Name,Display Name,Username,Email Address,',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_account_detail_shortcode",
                        "heading" => false,
                        'param_name' => 'social_fields',
                        'value' => '',
                        'descripiton' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }

    public function ARM_arm_account_detail_html($settings, $value) {
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans,$arm_members_directory,$arm_social_feature;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class="' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_account_detail]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Account Detail', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Display user\'s account details', 'ARMember');        ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_account_detail" style="width: 785px;">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Profile Fields', 'ARMember'); ?></th>
                                                <td class="arm_view_profile_wrapper">
                                                    <div class="arm_social_profile_fields_selection_wrapper">
                                                    <input type="hidden" name="label" class="wpb_vc_param_value" id="arm_profile_label_hidden" value="" />
                                                    <input type="hidden" name="value" class="wpb_vc_param_value" id="arm_profile_value_hidden" value="" />
                                                    <?php
                                                    $dbProfileFields = $arm_members_directory->arm_template_profile_fields();
                                                    if (!empty($dbProfileFields)){ ?>
                                                        <?php $i=1; foreach ($dbProfileFields as $fieldMetaKey => $fieldOpt){ ?>
                                                        <?php
                                                        if (empty($fieldMetaKey) || $fieldMetaKey == 'user_pass' || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                                            continue;
                                                        }
                                                        $fchecked = '';
                                                        if (in_array($fieldMetaKey, array('user_email', 'user_login', 'first_name', 'last_name'))) {
                                                            $fchecked = 'checked="checked"';
                                                        }
                                                        ?>
                                                        <label class="account_detail_radio arm_account_detail_options arm_acount_field_details_option">
                                                            <input type="checkbox" id="arm_account_detail_<?php echo $fieldMetaKey; ?>" name="fields[]" value="<?php echo $fieldMetaKey; ?>" <?php echo $fchecked; ?> onchange="arm_account_detail_tab_func();" class="arm_account_detail_fields arm_account_chk_fields arm_icheckbox" />
                                                            <input type="text" class="arm_account_detail_fields arm_account_detail_input" onkeyup="arm_account_detail_tab_func()" name="value[]" value="<?php echo $fieldOpt['label']; ?>" />
                                                        </label>
                                                        <?php $i++; }
                                                    }
                                                    ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php if( $arm_social_feature->isSocialFeature ): ?>
                                            <tr>
                                                <th><?php _e('Social Profile Fields','ARMember'); ?></th>
                                                <td class='arm_view_profile_wrapper'>
                                                    <input type='hidden' name='social_fields' id='profile_social_fields_hidden' class='wpb_vc_param_value' value='' />
                                                    <div class="arm_social_profile_fields_selection_wrapper">
                                                        <?php
                                                        $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
                                                        if (!empty($socialProfileFields)) {
                                                            foreach ($socialProfileFields as $spfKey => $spfLabel) {
                                                                ?><label class="account_detail_radio arm_account_detail_options">
                                                                    <input type="checkbox" class="arm_icheckbox arm_spf_profile_fields" value="<?php echo $spfKey;?>" name="social_fields[]" id="arm_spf_<?php echo $spfKey;?>_status" onchange="arm_select_profile_social_fields()" >
                                                                    <span><?php echo $spfLabel;?></span>
                                                                </label><?php
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_close_account() {
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Close Account', 'ARMember'),
                'description' => '',
                'base' => 'arm_close_account',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_close_account_shortcode",
                        'heading' => false,
                        'param_name' => 'set_id',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_close_account_shortcode",
                        'heading' => false,
                        'param_name' => 'css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }

    public function ARM_arm_close_account_html($settings, $value) {
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />

            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_close_account]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Close Account', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <!--<div class="arm_shortcode_description">< ?php _e('Close Account Shortcode', 'ARMember'); ?></div> -->
                                <div class="arm_shortcode_generator_form arm_generator_arm_close_account">
                                    <form onsubmit="return false;">
                                        <?php
                                        $setnames= $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type` = 'login' GROUP BY arm_set_id ORDER BY arm_form_id ASC");
                                        ?>
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th> <?php _e('Select set of login form','ARMember'); ?> </th>
                                                <td>
                                                    <input type="hidden" name="set_id" class="wpb_vc_param_value" id="arm_set_id" onchange="arm_show_hide_css_textarea(this.value)" />
                                                    <dl class="arm_selectbox column_level_dd arm_set_id_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul class="arm_set_id" data-id="arm_set_id">
                                                                <li data-label="<?php _e('Select Form', 'ARMember'); ?>" data-value=""><?php _e('Select Form', 'ARMember'); ?></li>
                                                                <?php if(!empty($setnames)):?>
                                                                    <?php foreach($setnames as $sn): ?>
                                                                        <li data-label="<?php echo stripslashes($sn->arm_set_name);?>" data-value="<?php echo $sn->arm_form_id;?>"><?php echo stripslashes($sn->arm_set_name);?></li>
                                                                    <?php endforeach;?>
                                                                <?php endif;?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            <tr id="arm_close_acc_css" style="display:none;">
                                                <th> <?php _e('Custom CSS', 'ARMember'); ?> </th>
                                                <td>
                                                    <textarea class="arm_popup_textarea wpb_vc_param_value" id="arm_cancel_link_css" name="css" rows="3"></textarea>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }


    public function ARM_arm_membership(){
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Current Membership', 'ARMember'),
                'description' => '',
                'base' => 'arm_membership',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_membership_shortcode",
                        'heading' => false,
                        'param_name' => 'title',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_membership_shortcode",
                        'heading' => false,
                        'param_name' => 'setup_id',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array( 
                        "type" => "ARM_arm_membership_shortcode",
                        "heading" => false,
                        "param_name" => 'display_renew_button',
                        'value' => 'false',
                        'description' => '&nbsp;',
                        'admin_label' => true,
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'renew_text',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                     array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'make_payment_text',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'renew_css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),   
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'renew_hover_css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                     array( 
                        "type" => "ARM_arm_membership_shortcode",
                        "heading" => false,
                        "param_name" => 'display_cancel_button',
                        'value' => 'false',
                        'description' => '&nbsp;',
                        'admin_label' => true,
                        ),
                    array(   
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'cancel_text',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'cancel_css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),   
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'cancel_hover_css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array( 
                        "type" => "ARM_arm_membership_shortcode",
                        "heading" => false,
                        "param_name" => 'display_update_card_button',
                        'value' => 'false',
                        'description' => '&nbsp;',
                        'admin_label' => true,
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'update_card_text',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'update_card_css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),   
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'update_card_hover_css',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'trial_active',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'message_no_record',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'cancel_message',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array(
                        "type" => "ARM_arm_membership_shortcode",
                        'heading' => false,
                        'param_name' => 'membership_label',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                    array(
                        'type' => 'ARM_arm_membership_shortcode',
                        'heading' => false,
                        'param_name' => 'membership_value',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                        ),
                )
            ));
        }
    }

    public function ARM_arm_membership_html($settings,$value){
        global $wpdb, $ARMember;
        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';
        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_current_membership]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php //_e('ARMember Cancel Membership', 'ARMember'); ?><?php _e('ARMember Current Membership', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_membership" style="width:660px">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                        <tr>
                                            <th><?php _e('Title','ARMember'); ?></th>
                                            <td>
                                                <input type="text" class="wpb_vc_param_value" id='current_membership_label' name="title" value="<?php _e('Current Membership','ARMember'); ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php _e('Select Setup','ARMember'); ?></th>
                                            <td>
                                                <?php
                                                $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMember->tbl_arm_membership_setup."` ");
                                                $armsteuplist = '';
                                                if (!empty($setups)) {
                                                    foreach ($setups as $ms) {
                                                        $armsteuplist .= '<li class="arm_shortcode_form_id_li" data-label="' . stripslashes($ms->arm_setup_name) . '" data-value="' . $ms->arm_setup_id . '">' . stripslashes($ms->arm_setup_name) . '</li>';
                                                    }
                                                }
                                                ?>
                                                <input type="hidden" id="arm_form_select" class="wpb_vc_param_value" name="setup_id" value=""/>
                                                <dl class="arm_selectbox column_level_dd" id="arm_form_select_dropdown">
                                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                    <dd>
                                                        <ul class="arm_form_select" data-id="arm_form_select">
                                                            <li data-label="<?php _e('Select Form', 'ARMember'); ?>" data-value=""><?php _e('Select Form', 'ARMember'); ?></li>
                                                            <?php echo $armsteuplist; ?>
                                                        </ul>
                                                    </dd>
                                                </dl>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php _e('Current Membership','ARMember'); ?></th>
                                            <td>
                                                <input type="hidden" class="wpb_vc_param_value" name='membership_label' id="arm_current_membership_fields_label" value="current_membership_no,current_membership_is,current_membership_recurring_profile,current_membership_started_on,current_membership_expired_on,current_membership_next_billing_date,action_button" />
                                                <input type="hidden" class="wpb_vc_param_value" name='membership_value' id="arm_current_membership_fields_value" value="No.,Membership Plan,Plan Type,Starts On,Expires On,Cycle Date,Action," />
                                                <ul class="arm_member_current_membership_fields">
                                                        <li class="arm_member_current_membership_field_list">
                                                                <label class="arm_member_current_membership_field_item">
                                                                        <input type="checkbox" class="arm_icheckbox arm_current_membership_field_input arm_member_current_membership_fields" name="arm_current_membership_fields[]" id="current_membership_no" value="current_membership_no" checked="checked" onchange="arm_select_current_membership_fields()" />
                                                                </label>
                                                                <input type="text" id="current_membership_no_text" class="arm_member_current_membership_fields arm_text_input" name="arm_current_membership_field_label_current_membership_no" value="<?php _e('No.','ARMember'); ?>" onkeyup="arm_select_current_membership_fields()" />
                                                        </li>
                                                        <li class="arm_member_current_membership_field_list">
                                                                <label class="arm_member_current_membership_field_item">
                                                                        <input type="checkbox" class="arm_icheckbox arm_current_membership_field_input arm_member_current_membership_fields" name="arm_current_membership_fields[]" id="current_membership_is" value="current_membership_is" checked="checked" onchange="arm_select_current_membership_fields()" />
                                                                </label>
                                                                <input type="text" class="arm_member_current_membership_fields arm_text_input" id="current_membership_is_text" name="arm_current_membership_field_label_current_membership_is" value="<?php _e('Membership Plan','ARMember'); ?>" onkeyup="arm_select_current_membership_fields()" />
                                                        </li>
                                                        <li class="arm_member_current_membership_field_list">
                                                                <label class="arm_member_current_membership_field_item">
                                                                        <input type="checkbox" class="arm_icheckbox arm_current_membership_field_input arm_member_current_membership_fields" name="arm_current_membership_fields[]" id="current_membership_recurring_profile" value="current_membership_recurring_profile" checked="checked" onchange="arm_select_current_membership_fields()" />
                                                                </label>
                                                                <input type="text" class="arm_member_current_membership_fields arm_text_input" id="current_membership_recurring_profile_text" name="arm_current_membership_field_label_current_membership_recurring_profile" value="<?php _e('Plan Type','ARMember'); ?>" onkeyup="arm_select_current_membership_fields()" />
                                                        </li>
                                                        <li class="arm_member_current_membership_field_list">
                                                                <label class="arm_member_current_membership_field_item">
                                                                        <input type="checkbox" class="arm_icheckbox arm_current_membership_field_input arm_member_current_membership_fields" name="arm_current_membership_fields[]" id="current_membership_started_on" value="current_membership_started_on" checked="checked" onchange="arm_select_current_membership_fields()" />
                                                                </label>
                                                                <input type="text" id="current_membership_started_on_text" class="arm_member_current_membership_fields arm_text_input" name="arm_current_membership_field_label_current_membership_started_on" value="<?php _e('Starts On','ARMember'); ?>" onkeyup="arm_select_current_membership_fields()" />
                                                        </li>
                                                        <li class="arm_member_current_membership_field_list">
                                                                <label class="arm_member_current_membership_field_item">
                                                                        <input type="checkbox" class="arm_icheckbox arm_current_membership_field_input arm_member_current_membership_fields" name="arm_current_membership_fields[]" id="current_membership_expired_on" value="current_membership_expired_on" checked="checked" onchange="arm_select_current_membership_fields()" />
                                                                </label>
                                                                <input type="text" class="arm_member_current_membership_fields arm_text_input" id="current_membership_expired_on_text" name="arm_current_membership_field_label_current_membership_expired_on" value="<?php _e('Expires On','ARMember'); ?>" onkeyup="arm_select_current_membership_fields()" />
                                                        </li>
                                                        <li class="arm_member_current_membership_field_list">
                                                                <label class="arm_member_current_membership_field_item">
                                                                        <input type="checkbox" class="arm_icheckbox arm_current_membership_field_input arm_member_current_membership_fields" name="arm_current_membership_fields[]" id="current_membership_next_billing_date" value="current_membership_next_billing_date" checked="checked" onchange="arm_select_current_membership_fields()" />
                                                                </label>
                                                                <input type="text" class="arm_member_current_membership_fields arm_text_input" id="current_membership_next_billing_date_text" name="arm_current_membership_field_label_current_membership_next_billing_date" value="<?php _e('Cycle Date','ARMember'); ?>" onkeyup="arm_select_current_membership_fields()" />
                                                        </li>
                                                        
                                                        <li class="arm_member_current_membership_field_list">
                                                                <label class="arm_member_current_membership_field_item">
                                                                        <input type="checkbox" class="arm_icheckbox arm_current_membership_field_input arm_member_current_membership_fields" name="arm_current_membership_fields[]" id="action_button" value="action_button" checked="checked" onchange="arm_select_current_membership_fields()" />
                                                                </label>
                                                                <input type="text" class="arm_member_current_membership_fields arm_text_input" id="action_button_text" name="arm_current_membership_field_label_action_button" value="<?php _e('Action','ARMember'); ?>" onkeyup="arm_select_current_membership_fields()" />
                                                        </li>
                                                        
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php _e('Display Renew Subscription Button','ARMember'); ?></th>
                                            <td>
                                                <input type="hidden" name="display_renew_button" value="false" class="wpb_vc_param_value" id="arm_show_renew_subscription_hidden" />
                                                <label class="form_show_renew_subscription_type_radio">
                                                <input type="radio" name="arm_show_renew_subscription_input" value="false" checked="checked" id="arm_show_renew_subscription_hidden_false" onclick="arm_show_renew_subscription();" class="arm_iradio" />
                                                    <?php _e('No', 'ARMember'); ?>
                                                </label>
                                                <label class="form_show_renew_subscription_type_radio">
                                                    <input type="radio" name="arm_show_renew_subscription_input" value="true" id="arm_show_renew_subscription_true" onclick="arm_show_renew_subscription();" class="arm_iradio" />
                                                    <?php _e('Yes', 'ARMember'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_renew_subscription_section">
                                            <th><?php _e('Button Text','ARMember'); ?></th>
                                            <td>
                                                <input type="text" class="wpb_vc_param_value" name="renew_text" value="<?php _e('Renew','ARMember'); ?>" id="arm_renew_membership_text" />
                                            </td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_renew_subscription_section">
                                            <th><?php _e('Make Payment Text','ARMember'); ?></th>
                                            <td>
                                                <input type="text" class="wpb_vc_param_value" name="make_payment_text" value="<?php _e('Make Payment','ARMember'); ?>" id="arm_make_payment_membership_text" />
                                            </td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_renew_subscription_section">
                                            <th><?php _e('Button CSS','ARMember'); ?></th>
                                            <td><textarea class="arm_popup_textarea wpb_vc_param_value" name="renew_css" id="arm_button_css" rows="3"></textarea></td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_renew_subscription_section">
                                            <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                            <td><textarea class="arm_popup_textarea wpb_vc_param_value" name="renew_hover_css" id="arm_button_hover_css" rows="3"></textarea></td>
                                            </tr> 
                                        <tr> 
                                            <th><?php _e('Display Cancel Subscription Button?','ARMember'); ?></th>
                                            <td>
                                                <input type="hidden" name="display_cancel_button" value="false" class="wpb_vc_param_value" id="arm_show_cancel_subscription_hidden" />
                                                <label class="form_show_cancel_subscription_type_radio">
                                                <input type="radio" name="arm_show_cancel_subscription_input" value="false" checked="checked" id="arm_show_cancel_subscription_hidden_false" onclick="arm_show_cancel_subscription();" class="arm_iradio" />
                                                    <?php _e('No', 'ARMember'); ?>
                                                </label>
                                                <label class="form_show_cancel_subscription_type_radio">
                                                    <input type="radio" name="arm_show_cancel_subscription_input" value="true" id="arm_show_cancel_subscription_true" onclick="arm_show_cancel_subscription();" class="arm_iradio" />
                                                    <?php _e('Yes', 'ARMember'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_cancel_subscription_section">
                                            <th><?php _e('Button Text','ARMember'); ?></th>
                                            <td>
                                                <input type="text" class="wpb_vc_param_value" name="cancel_text" value="<?php _e('Cancel','ARMember'); ?>" id="arm_cancel_membership_text" />
                                            </td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_cancel_subscription_section">
                                            <th><?php _e('Button CSS','ARMember'); ?></th>
                                            <td><textarea class="arm_popup_textarea wpb_vc_param_value" name="cancel_css" id="arm_cancel_button_css" rows="3"></textarea></td>
                                        </tr> 
                                        <tr class="form_popup_options" id="show_cancel_subscription_section">
                                            <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                            <td><textarea class="arm_popup_textarea wpb_vc_param_value" name="cancel_hover_css" id="arm_cancel_button_hover_css" rows="3"></textarea></td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_cancel_subscription_section">
                                            <th><?php _e('Subscription Cancelled Message','ARMember'); ?></th>
                                            <td><input type="text" class="wpb_vc_param_value" name="cancel_message" value="<?php _e('Your subscription has been cancelled.','ARMember'); ?>" id="arm_cancel_message" />
                                        </tr>
                                        <tr>
                                            <th><?php _e('Display Update Card Subscription Button?','ARMember'); ?></th>
                                            <td>
                                                <input type="hidden" name="display_update_card_button" value="true" class="wpb_vc_param_value" id="arm_show_update_card_subscription_hidden" />
                                                <label class="form_show_update_card_subscription_type_radio">
                                                <input type="radio" name="arm_show_update_card_subscription_input" value="false" checked="checked" id="arm_show_update_card_subscription_hidden_false" onclick="arm_show_update_card_subscription();" class="arm_iradio" />
                                                    <?php _e('No', 'ARMember'); ?>
                                                </label>
                                                <label class="form_show_update_card_subscription_type_radio">
                                                    <input type="radio" name="arm_show_update_card_subscription_input" value="true" id="arm_show_update_card_subscription_true" onclick="arm_show_update_card_subscription();" class="arm_iradio" />
                                                    <?php _e('Yes', 'ARMember'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_update_card_subscription_section">
                                            <th><?php _e('Update Card Text','ARMember'); ?></th>
                                            <td>
                                                <input type="text" class="wpb_vc_param_value" name="update_card_text" value="<?php _e('Update Card','ARMember'); ?>" id="arm_update_card_membership_text" />
                                            </td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_update_card_subscription_section">
                                            <th><?php _e('Button CSS','ARMember'); ?></th>
                                            <td><textarea class="arm_popup_textarea wpb_vc_param_value" name="update_card_css" id="arm_update_card_button_css" rows="3"></textarea></td>
                                        </tr>
                                        <tr class="form_popup_options" id="show_update_card_subscription_section">
                                            <th><?php _e('Button Hover CSS','ARMember'); ?></th>
                                            <td><textarea class="arm_popup_textarea wpb_vc_param_value" name="update_card_hover_css" id="arm_update_card_button_hover_css" rows="3"></textarea></td>
                                        </tr> 
                                        <tr>
                                            <th><?php _e('Trial Active Label','ARMember'); ?></th>
                                            <td><input type="text" class="wpb_vc_param_value" name="trial_active" value="<?php _e('trial active','ARMember'); ?>" id="arm_trial_active" />
                                        </tr>
                                        <tr>
                                            <th><?php _e('No Records Message','ARMember'); ?></th>
                                            <td><input type="text" class="wpb_vc_param_value" name="message_no_record" value="<?php _e('There is no membership found.','ARMember'); ?>" id="arm_message_no_record" />
                                        </tr>
                                    </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_login_history(){
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Login History', 'ARMember'),
                'description' => '',
                'base' => 'arm_login_history',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_login_history_shortcode",
                        'heading' => false,
                        'param_name' => 'label',
                        'value' => 'user,logged_in_date,logged_in_ip,logged_in_using,logged_out_date,login_duration,',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_login_history_shortcode',
                        'heading' => false,
                        'param_name' => 'value',
                        'value' => 'User,Logged in Date,Logged in IP,Logged in Using,Logged out date,Login Duration,',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                )
            ));
        }
    }

    public function ARM_arm_login_history_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <script type="text/javascript">
                __LOGIN_HISTORY_FIELD_VALUES = "User,Logged in Date,Logged in IP,Logged in Using,Logged out date,Login Duration,";
            </script>
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_member_transaction]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Login History', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Display user\'s activities', 'ARMember');        ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_login_history">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Login History','ARMember'); ?></th>
                                                <td>
                                                    <input type='hidden' name='label' class='wpb_vc_param_value' id='arm_login_history_label_hidden' value='' />
                                                    <input type='hidden' name='value' class='wpb_vc_param_value' id='arm_login_history_value_hidden' value='' />
                                                    <ul class="arm_member_login_history_fields">
                                                        <li class="arm_member_login_history_field_list">
                                                            <label class="arm_member_login_history_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_login_history_fields arm_member_login_history_field_input" name="arm_login_history_fields[]" value="user" checked="checked" onchange="arm_select_login_history_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_login_history_fields" onkeyup="arm_select_login_history_fields()" name="value[]" value="<?php _e('User','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_login_history_field_list">
                                                            <label class="arm_member_login_history_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_login_history_fields arm_member_login_history_field_input" name="arm_login_history_fields[]" value="logged_in_date" checked="checked" onchange="arm_select_login_history_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_login_history_fields" onkeyup="arm_select_login_history_fields()" name="value[]" value="<?php _e('Logged in Date','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_login_history_field_list">
                                                            <label class="arm_member_login_history_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_login_history_fields arm_member_login_history_field_input" name="arm_login_history_fields[]" value="logged_in_ip" checked="checked" onchange="arm_select_login_history_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_login_history_fields" onkeyup="arm_select_login_history_fields()" name="value[]" value="<?php _e('Logged in IP','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_login_history_field_list">
                                                            <label class="arm_member_login_history_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_login_history_fields arm_member_login_history_field_input" name="arm_login_history_fields[]" value="logged_in_using" checked="checked" onchange="arm_select_login_history_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_login_history_fields" onkeyup="arm_select_login_history_fields()" name="value[]" value="<?php _e('Logged in Using','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_login_history_field_list">
                                                            <label class="arm_member_login_history_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_login_history_fields arm_member_login_history_field_input" name="arm_login_history_fields[]" value="logged_out_date" checked="checked" onchange="arm_select_login_history_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_login_history_fields" onkeyup="arm_select_login_history_fields()" name="value[]" value="<?php _e('Logged Out Date','ARMember'); ?>" />
                                                        </li>
                                                        <li class="arm_member_login_history_field_list">
                                                            <label class="arm_member_login_history_field_item">
                                                                <input type="checkbox" class="arm_icheckbox arm_member_login_history_fields arm_member_login_history_field_input" name="arm_login_history_fields[]" value="login_duration" checked="checked" onchange="arm_select_login_history_fields()" />
                                                            </label>
                                                            <input type="text" class="arm_member_login_history_fields" onkeyup="arm_select_login_history_fields()" name="value[]" value="<?php _e('Login Duration','ARMember'); ?>" />
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_conditional_redirection(){
        global $arm_version,$ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Conditional Redirect', 'ARMember'),
                'description' => '',
                'base' => 'arm_conditional_redirection',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_conditional_redirection_shortcode",
                        'heading' => false,
                        'param_name' => 'condition',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_conditional_redirection_shortcode',
                        'heading' => false,
                        'param_name' => 'plans',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_conditional_redirection_shortcode',
                        'heading' => false,
                        'param_name' => 'redirect_to',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }
    
    public function ARM_arm_conditional_redirection_role(){
        global $arm_version,$ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Conditional Redirect (User Role)', 'ARMember'),
                'description' => '',
                'base' => 'arm_conditional_redirection_role',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_conditional_redirection_role_shortcode",
                        'heading' => false,
                        'param_name' => 'condition',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_conditional_redirection_role_shortcode',
                        'heading' => false,
                        'param_name' => 'roles',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'ARM_arm_conditional_redirection_role_shortcode',
                        'heading' => false,
                        'param_name' => 'redirect_to',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }

    public function ARM_arm_conditional_redirection_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;
        $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `" . $ARMember->tbl_arm_membership_setup . "` ");
        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_member_transaction]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Conditional Redirect', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Display user\'s activities', 'ARMember');        ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_conditional_redirection">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Condition','ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" id="arm_conditional_redirection_condition" class="wpb_vc_param_value" name="condition" value="" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirection_condition">
                                                                <li data-label="<?php _e('Select Form', 'ARMember'); ?>" data-value=""><?php _e('Select Option', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('Having','ARMember'); ?>" data-value="having"><?php _e('Having','ARMember'); ?></li>
                                                                <li data-label="<?php _e('Not Having','ARMember'); ?>" data-value="nothaving"><?php _e('Not Having','ARMember'); ?></li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Plans','ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" id="arm_conditional_redirection_plans" class="wpb_vc_param_value" name="plans" value="" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirection_plans">
                                                                <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Plan', 'ARMember'); ?></li>
                                                                <?php 
                                                                $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
                                                                $arm_planlist = '';
                                                                if(!empty($all_plans)): 
                                                                    foreach($all_plans as $plan):
                                                                        $arm_planlist .= '<li class="arm_shortcode_form_id_li" data-label="' . stripslashes($plan['arm_subscription_plan_name']) . '" data-value="' . $plan['arm_subscription_plan_id'] . '">' . stripslashes($plan['arm_subscription_plan_name']) . '</li>';
                                                                        
                                                                    endforeach;
                                                                endif;
                                                                echo $arm_planlist;
                                                                ?>
                                                                <li data-label="<?php _e('Non Logged in Users','ARMember') ?>" data-value="not_logged_in"><?php _e('Non Logged in Users','ARMember') ?></li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Redirect URL','ARMember'); ?></th>
                                                <td>
                                                    <input type="text" class="wpb_vc_param_value" name="redirect_to" value="" id="arm_conditional_redirection_url" /><br/>
                                                    <span><i><?php echo __('Please Enter URL with','ARMember').' http:// or https://'; ?></i></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    public function ARM_arm_conditional_redirection_role_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;
        $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `" . $ARMember->tbl_arm_membership_setup . "` ");
        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';
        $all_roles = $arm_global_settings->arm_get_all_roles();

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_member_transaction]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Conditional Redirect (User Role)', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Display user\'s activities', 'ARMember');        ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_conditional_redirection_role">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th><?php _e('Condition','ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" id="arm_conditional_redirection_condition_role" class="wpb_vc_param_value" name="condition" value="" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirection_condition_role">
                                                                <li data-label="<?php _e('Select Form', 'ARMember'); ?>" data-value=""><?php _e('Select Option', 'ARMember'); ?></li>
                                                                <li data-label="<?php _e('Having','ARMember'); ?>" data-value="having"><?php _e('Having','ARMember'); ?></li>
                                                                <li data-label="<?php _e('Not Having','ARMember'); ?>" data-value="nothaving"><?php _e('Not Having','ARMember'); ?></li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Roles','ARMember'); ?></th>
                                                <td>
                                                    <input type="hidden" id="arm_conditional_redirection_roles" class="wpb_vc_param_value" name="roles" value="" />
                                                    <dl class="arm_selectbox column_level_dd">
                                                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                        <dd>
                                                            <ul data-id="arm_conditional_redirection_roles">
                                                                <li data-label="<?php _e('Select Role', 'ARMember'); ?>" data-value=""><?php _e('Select Role', 'ARMember'); ?></li>
                                                                <?php if (!empty($all_roles)): ?>
                                                                
                                                                <?php foreach ($all_roles as $role_key => $role_name): ?>
                                                                        <li data-label="<?php echo (stripslashes($role_name)); ?>" data-value="<?php echo $role_key; ?>"><?php echo (stripslashes($role_name)); ?></li>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                                
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Redirect URL','ARMember'); ?></th>
                                                <td>
                                                    <input type="text" class="wpb_vc_param_value" name="redirect_to" value="" id="arm_conditional_redirection_url" /><br/>
                                                    <span><i><?php echo __('Please Enter URL with','ARMember').' http:// or https://'; ?></i></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_last_login_history(){
        global $arm_version,$ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Last Login Data', 'ARMember'),
                'description' => '',
                'base' => 'arm_last_login_history',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array()
            ));
        }
    }

    public function ARM_arm_last_login_history_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Last Login data', 'ARMember'); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    public function ARM_arm_username(){
        global $arm_version,$ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember Username', 'ARMember'),
                'description' => '',
                'base' => 'arm_username',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_username_shortcode",
                        'heading' => false,
                        'param_name' => 'arm_username',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                )
            ));
        }
    }
    public function ARM_arm_username_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember Username', 'ARMember'); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    public function ARM_arm_user_plan(){
        global $arm_version,$ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember User Plan', 'ARMember'),
                'description' => '',
                'base' => 'arm_user_plan',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_user_plan_shortcode",
                        'heading' => false,
                        'param_name' => 'arm_user_plan',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                )
            ));
        }
    }
    public function ARM_arm_user_plan_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;
        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';
        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember User Plan', 'ARMember'); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    public function ARM_arm_displayname(){
        global $arm_version,$ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember User Displayname', 'ARMember'),
                'description' => '',
                'base' => 'arm_displayname',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_displayname_shortcode",
                        'heading' => false,
                        'param_name' => 'arm_userdisplay',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                )
            ));
        }
    }
    public function ARM_arm_displayname_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember User Displayname', 'ARMember'); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_firstname_lastname(){
        global $arm_version,$ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember User Firstname Lastname', 'ARMember'),
                'description' => '',
                'base' => 'arm_firstname_lastname',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_firstname_lastname_shortcode",
                        'heading' => false,
                        'param_name' => 'arm_firstname_lastname',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                )
            ));
        }
    }
    public function ARM_arm_firstname_lastname_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember User Firstname Lastname', 'ARMember'); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_avatar(){
        global $arm_version,$ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember User Avatar', 'ARMember'),
                'description' => '',
                'base' => 'arm_avatar',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_avatar_shortcode",
                        'heading' => false,
                        'param_name' => 'arm_user_avatar',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                )
            ));
        }
    }
    public function ARM_arm_avatar_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block a  ccordion_menu">
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember User Avatar', 'ARMember'); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function ARM_arm_usermeta(){
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember User Custom Meta', 'ARMember'),
                'description' => '',
                'base' => 'arm_usermeta',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_usermeta_shortcode",
                        'heading' => false,
                        'param_name' => 'meta',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }
    public function ARM_arm_usermeta_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />

            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_custome_user_meta]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember User Custom Meta', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_generator_form arm_generator_arm_usermeta">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th> <?php _e('Enter Usermeta Name', 'ARMember'); ?> </th>
                                                <td>
                                                    <input type="text" class="wpb_vc_param_value" name="meta" value="" id="arm_user_custom_meta" />
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    public function ARM_arm_user_badge(){
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember User Badge', 'ARMember'),
                'description' => '',
                'base' => 'arm_user_badge',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_user_badge_shortcode",
                        'heading' => false,
                        'param_name' => 'user_id',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }
    public function ARM_arm_user_badge_html($settings,$value){
        global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';

        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />

            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_user_badge]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember User Badge', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_generator_form arm_generator_arm_user_badge">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                            <tr>
                                                <th> <?php _e('Enter User Id', 'ARMember'); ?> </th>
                                                <td>
                                                    <input type="text" class="wpb_vc_param_value" name="user_id" value="" id="arm_user_id" />
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    public function ARM_arm_user_planinfo(){
        global $arm_version, $ARMember;
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => __('ARMember User Plan Information', 'ARMember'),
                'description' => '',
                'base' => 'arm_user_planinfo',
                'category' => __('ARMember', 'ARMember'),
                'class' => '',
                'controls' => 'full',
                'icon' => 'arm_vc_icon',
                'admin_enqueue_css' => array(MEMBERSHIP_URL . '/core/vc/arm_vc.css'),
                'front_enqueue_css' => MEMBERSHIP_URL . '/core/vc/arm_vc.css',
                'params' => array(
                    array(
                        "type" => "ARM_arm_user_planinfo_shortcode",
                        'heading' => false,
                        'param_name' => 'plan_id',
                        'value' => '',
                        'description' => '&nbsp;',
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARM_arm_user_planinfo_shortcode",
                        'heading' => false,
                        'param_name' => 'plan_info',
                        'value' => false,
                        'description' => '&nbsp;',
                        'admin_label' => true
                    )
                )
            ));
        }
    }
    public function ARM_arm_user_planinfo_html($settings,$value){
         global $wpdb, $ARMember, $arm_slugs, $arm_shortcodes, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_member_forms, $arm_subscription_plans;
         
        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';
         
        $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
        if ($this->is_membership_vdextend == 0) {
            $this->is_membership_vdextend = 1;
            ?>
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm-font-awesome.css" />
            <link rel="stylesheet" href="<?php echo MEMBERSHIP_URL; ?>/css/arm_tinymce.css" />
            <div class="arm_tinymce_shortcode_options_container arm_shortcode_options_popup_wrapper">
                <div class="arm_tinymce_content_block accordion_menu">
                    <!-- *********************[arm_setup]********************* -->
                    <div class="arm_tinymce_shortcode_content">
                        <div class="arm_shortcode_content_header accordion_header" style="box-shadow:none;border-bottom: none;"><?php _e('ARMember User Plan Information', 'ARMember'); ?></div>
                        <div class="arm_shortcode_detail_wrapper">
                            <div class="arm_shortcode_detail_container">
                                <div class="arm_shortcode_description"><?php // _e('Membership Setup Wizard Shortcode.', 'ARMember'); ?></div>
                                <div class="arm_shortcode_generator_form arm_generator_arm_setup" style="width:660px;">
                                    <form onsubmit="return false;">
                                        <table class="arm_shortcode_option_table">
                                                <tr>
                                                    <th><?php _e('Select Membership Plan', 'ARMember');?></th>
                                                    <td>
                                                        <input type='hidden' class="wpb_vc_param_value" name="plan_id" id="arm_plan_id" value=""/>
                                                        <dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
                                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                            <dd><ul data-id="arm_plan_id">
                                                            <li data-label="<?php _e('Select Plan', 'ARMember'); ?>" data-value=""><?php _e('Select Plan', 'ARMember'); ?></li>
                                                            <?php    foreach ($all_plans as $p) {
                                                                echo '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . $p['arm_subscription_plan_id']. '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                                                            }?>
                                                            </ul></dd>
                                                        </dl>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php _e('Select Plan Information', 'ARMember');?></th>
                                                    <td>
                                                        <input type='hidden' class="wpb_vc_param_value" name="plan_info" id="plan_info" value="start_date"/>
                                                        <dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
                                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                            <dd><ul data-id="plan_info">
                                                                    <li data-label="<?php _e('Start Date', 'ARMember'); ?>" data-value="arm_start_plan"><?php _e('Start Date', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('End Date', 'ARMember'); ?>" data-value="arm_expire_plan"><?php _e('End Date', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('Trial Start Date', 'ARMember'); ?>" data-value="arm_trial_start"><?php _e('Trial Start Date', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('Trial End Date', 'ARMember'); ?>" data-value="arm_trial_end"><?php _e('Trial End Date', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('Grace End Date', 'ARMember'); ?>" data-value="arm_grace_period_end"><?php _e('Grace End Date', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('Paid By', 'ARMember'); ?>" data-value="arm_user_gateway"><?php _e('Paid By', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('Completed Recurrence', 'ARMember'); ?>" data-value="arm_completed_recurring"><?php _e('Completed Recurrence', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('Next Due Date', 'ARMember'); ?>" data-value="arm_next_due_payment"><?php _e('Next Due Date', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('Payment Mode', 'ARMember'); ?>" data-value="arm_payment_mode"><?php _e('Payment Mode', 'ARMember'); ?></li>
                                                                    <li data-label="<?php _e('Payment Cycle', 'ARMember'); ?>" data-value="arm_payment_cycle"><?php _e('Payment Cycle', 'ARMember'); ?></li>
                                                                </ul></dd>
                                                        </dl>
                                                    </td>
                                                </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}?>