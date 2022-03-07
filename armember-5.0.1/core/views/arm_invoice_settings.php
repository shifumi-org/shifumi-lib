<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_member_forms;
$arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);
if($arm_invoice_tax_feature == 0) {
    wp_redirect(admin_url('admin.php?page=arm_general_settings'));
}
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$general_settings = $all_global_settings['general_settings'];
$general_settings['arm_invoice_template'] = isset($general_settings['arm_invoice_template']) ? $general_settings['arm_invoice_template'] : '';

$enable_tax= isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;

?>
<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">
   
		<form method="post" action="#" id="arm_invoice_settings" class="arm_invoice_settings arm_admin_form" onsubmit="return false;">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="arm_email_content_area_left">
                                        <?php 
                                        $arm_invoice_content = array('textarea_name' => 'arm_general_settings[arm_invoice_template]',
                                                'editor_class' => 'arm_invoice_template',
                                                'media_buttons' => true,
                                                'textarea_rows' => 25,
                                                'default_editor' => 'tinymce',
                                                'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',

                                        );
                                         wp_editor(stripslashes($general_settings['arm_invoice_template']), 'arm_invoice_content', $arm_invoice_content);
                                        ?>
                                        <span id="arm_comm_wp_validate_msg" class="error" style="display:none;"><?php _e('Content Cannot Be Empty.', 'ARMember');?></span>
                                    </div>
                                    <div class="arm_email_content_area_right">
                                            <span class="arm_sec_head"><?php _e('Template Tags', 'ARMember');?></span>
                                            <div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Username", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_USERNAME}"><?php _e("Username", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Firstname", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_USERFIRSTNAME}" ><?php _e("First Name", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display User Lastname", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_USERLASTNAME}" ><?php _e("Last Name", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan Name", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_SUBSCRIPTIONNAME}" ><?php _e("Plan Name", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan Description", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_SUBSCRIPTIONDESCRIPTION}" ><?php _e("Plan Description", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Gateway", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_GATEWAY}" ><?php _e("Payment Gateway", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Transaction ID", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_TRANSACTIONID}" ><?php _e("Transaction ID", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Invoice ID", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_INVOICEID}" ><?php _e("Invoice ID", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Subscription ID", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_SUBSCRIPTIONID}" ><?php _e("Subscription ID", 'ARMember'); ?></span>
                                                </div>
						
						<div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan Start Date", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_SUBSCRIPTION_START_DATE}"><?php _e("Plan Start Date", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan End Date", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_SUBSCRIPTION_END_DATE}"><?php _e("Plan End Date", 'ARMember'); ?></span>
                                                </div>
                                                
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payment Date", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_PAYMENTDATE}" ><?php _e("Payment Date", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Payer Email", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_PAYEREMAIL}" ><?php _e("Payer Email", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Plan Amount", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_AMOUNT}" ><?php _e("Amount", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Trial Amount", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_TRIALAMOUNT}" ><?php _e("Trial Amount", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Trial Period", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_TRIALPERIOD}" ><?php _e("Trial Period", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Coupon Code", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_COUPONCODE}"><?php _e("Coupon Code", 'ARMember'); ?></span>

                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Coupon Amount", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_COUPONAMOUNT}"><?php _e("Coupon Amount", 'ARMember'); ?></span>
                                                </div>
                                                <?php if($enable_tax == 1){?>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Tax Percentage", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_TAXPERCENTAGE}"><?php _e("Tax Percentage", 'ARMember'); ?></span>
                                                </div>
                                                <div class="arm_shortcode_row armhelptip" title="<?php _e("Display Tax Amount", 'ARMember'); ?>">
                                                    <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_TAXAMOUNT}"><?php _e("Tax Amount", 'ARMember'); ?></span>
                                                </div>
                                                <?php } ?>

                                                <?php
                                                    $dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
                                                    foreach ($dbFormFields as $meta_key => $field) {
                      
                                                        $field_options = maybe_unserialize($field);
                                                        $field_options = apply_filters('arm_change_field_options', $field_options);
                                                        $exclude_keys = array(
                                                            'first_name', 'last_name', 'user_login', 'user_email', 'user_pass', 'repeat_pass',
                                                            'arm_user_plan', 'arm_last_login_ip', 'arm_last_login_date', 'roles', 'section',
                                                            'repeat_pass', 'repeat_email', 'social_fields', 'avatar', 'profile_cover', 'user_pass_', 'display_name', 'description',
                                                        );
                                                        $meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                                        $label = isset($field_options['label']) ? $field_options['label'] : '';
                                                        $type = isset($field_options['type']) ? $field_options['type'] : array();
                                                       
                                                        if (!in_array($meta_key, $exclude_keys) && !in_array($type, array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email','arm_captcha'))) {
                                                ?>
                                                            <div class="arm_shortcode_row armhelptip" title="ARM_INVOICE_<?php echo trim($meta_key); ?>">
                                                                <span class="arm_variable_code arm_invoice_code" data-code="{ARM_INVOICE_<?php echo trim($meta_key); ?>}"><?php echo $label; ?> (<?php echo trim($meta_key); ?>)</span>
                                                            </div>
                                                <?php
                                                        }
                                                    }
                    
                                                ?>
                                                

                                            </div>
                                        </div>        
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                
                                <td>
                                    <span class="arm-note-message --warning"><?php printf( esc_html__('NOTE : Please add %sbr%s to use line break in plain text.','ARMember'),'&lt;','&gt;'); ?></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="arm_submit_btn_container">
                            <img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" id="arm_loader_img" class="arm_submit_btn_loader" style="display:none;" width="24" height="24" />&nbsp;<button id="arm_invoice_settings_btn" class="arm_save_btn" name="arm_invoice_settings_btn" type="submit"><?php _e('Save', 'ARMember') ?></button>
                    </div>
                    <?php wp_nonce_field( 'arm_wp_nonce' );?>
		</form>
	</div>
</div>
<?php 
        /* **********./Begin Bulk Delete Plan Popup/.********** */
        $invoice_reset_content = '<span class="arm_confirm_text">'.__("Are You sure you want to reset to default Invoice Template? After resetting template, all your changes will be gone and plugin's default Invoice template will be loaded. It is recommended to store current code before resetting template.",'ARMember' ).'</span>';
        $invoice_reset_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
        $invoice_reset_content_popup_arg = array(
            'id' => 'invoice_reset_content_message',
            'class' => 'invoice_reset_content_message',
                        'title' => __('Reset Invoice Template to Default','ARMember'),
            'content' => $invoice_reset_content,
            'button_id' => 'invoice_reset_ok_btn',
            'button_onclick' => "reset_to_default_invoice();",
        );
        echo $arm_global_settings->arm_get_bpopup_html($invoice_reset_content_popup_arg);
?>
<script type="text/javascript">
    var ARM_INVOICE_RESET_ERROR = "<?php _e('Sorry, something went wrong.', 'ARMember'); ?>";
</script>