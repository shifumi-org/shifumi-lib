<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_member_forms,$arm_pay_per_post_feature;

if(!$arm_pay_per_post_feature->isPayPerPostFeature):
     wp_redirect(admin_url('admin.php?page=arm_general_settings'));
endif;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$default_common_messages = $arm_global_settings->arm_default_common_messages();
$general_settings = $all_global_settings['general_settings'];
$general_settings['arm_pay_per_post_default_content'] = !empty($general_settings['arm_pay_per_post_default_content']) ? $general_settings['arm_pay_per_post_default_content'] : $default_common_messages['arm_pay_per_post_default_content'];


$arm_pay_per_post_buynow_var = (!empty($general_settings['arm_pay_per_post_buynow_var'])) ? $general_settings['arm_pay_per_post_buynow_var'] : 'arm_paid_post';

$arm_pay_per_post_allow_fancy_url = (!empty($general_settings['arm_pay_per_post_allow_fancy_url'])) ? $general_settings['arm_pay_per_post_allow_fancy_url'] : '';


$enable_tax= isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;

?>
<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">
   
		<form method="post" action="#" id="arm_pay_per_post_settings" class="arm_pay_per_post_settings arm_admin_form" onsubmit="return false;">
            <table class="form-table" width="100%">
                <tbody>
                    <tr>
                        <div class="page_sub_title"><?php _e('Paid Post Buy Now Settings', 'ARMember'); ?></div><br/>
                    </tr>
                    <tr class="form-field">
                        <th class="arm-form-table-label arm_paid_post_url_param">
                            <?php _e('Paid Post URL Parameter name', 'ARMember'); ?>  <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php _e('This parameter will be used while redirecting to the page when user will purchase specific post. By adding [arm_paid_post_buy_now] shortcode at the Alternative Content. If you have not setup page for \'Post Setup\' then please set \'post setup\' page from ARMember -> General Settings -> Page Setup page.', 'ARMember'); ?>"></i>
                        </th>
                        <td>
                            <input id="arm_pay_per_post_buynow_var" type="text" name="arm_general_settings[arm_pay_per_post_buynow_var]" value="<?php echo $arm_pay_per_post_buynow_var; ?>" >
                            <br/>
                            <span class="arm_info_text"><?php _e('Paid Post URL parameter name Ex. :', 'ARMember'); echo 'arm_paid_post'; ?></span><br/>
                        </td>
                    </tr>
                    <tr class="form-field" style="display: none;">
                        <th class="arm-form-table-label" style="vertical-align: baseline !important;">
                            <?php _e('Enable Fancy URL for Paid Post', 'ARMember'); ?>
                        </th>
                        <td class="arm-form-table-content" width="75%">
                            <div class="armswitch arm_global_setting_switch">
                                <input type="checkbox" id="arm_pay_per_post_allow_fancy_url" <?php checked($arm_pay_per_post_allow_fancy_url, '1');?> value="1" class="armswitch_input" name="arm_general_settings[arm_pay_per_post_allow_fancy_url]"/>
                                <label for="arm_pay_per_post_allow_fancy_url" class="armswitch_label"></label>
                            </div>

                            <p>&nbsp;</p>
                            <span class="arm_info_text arm_width_100_pct" ><?php _e('URL:', 'ARMember'); ?><code><span id="armpay_per_post_buynow_url_example"><?php echo ARM_HOME_URL.'/'; ?><?php echo ($arm_pay_per_post_allow_fancy_url == 1) ? ''.$arm_pay_per_post_buynow_var.'/' : '?'.$arm_pay_per_post_buynow_var.'='; ?>{post_id}</span></span></code>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <td colspan="2"><div class="page_sub_title"><?php _e('Default Alternative Content', 'ARMember'); ?></div></td>
                    </tr>
                    <tr class="form-field">
                        <td colspan="2">
                            <div class="arm_pay_per_post_default_content">
                                <?php 
                                $arm_pay_per_post_content = array(
                                    'textarea_name' => 'arm_general_settings[arm_pay_per_post_default_content]',
                                    'editor_class' => 'arm_pay_per_post_default_content',
                                    'textarea_rows' => 18,
                                    'default_editor' => 'tinymce',
                                    'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',

                                );
                                wp_editor(stripslashes($general_settings['arm_pay_per_post_default_content']), 'arm_pay_per_post_content', $arm_pay_per_post_content);
                                ?>
                                <span id="arm_comm_wp_validate_msg" class="error" style="display:none;"><?php _e('Content Cannot Be Empty.', 'ARMember');?></span>
                            </div>       
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="arm_submit_btn_container">
                    <img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button id="arm_pay_per_post_settings_btn" class="arm_save_btn" name="arm_pay_per_post_settings_btn" type="submit"><?php _e('Save', 'ARMember') ?></button>
            </div>
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
		</form>
	</div>
</div>
<script type="text/javascript">
    var ARM_PAY_PER_POST_RESET_ERROR = "<?php _e('Sorry, something went wrong.', 'ARMember'); ?>";
</script>