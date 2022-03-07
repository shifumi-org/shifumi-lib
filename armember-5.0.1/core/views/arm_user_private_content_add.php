<?php
global $wpdb, $ARMember;

if (isset($_POST['action']) && in_array($_POST['action'], array('add_private_content', 'edit_private_content'))) {
  
	do_action('arm_save_private_content', $_POST);
}

$form_mode = esc_html__("Add Userwise Private Content", 'ARMember');
$action = 'add_private_content';
$edit_mode = 0;

$member_id = "";
$private_content = "";
$enable_private_content = "1";
if (isset($_GET['action']) && $_GET['action'] == 'edit_private_content' && isset($_GET['member_id']) && !empty($_GET['member_id'])) {
	$member_id = intval($_GET['member_id']);
	$edit_mode = 1;
	$action = 'edit_private_content';
	$form_mode = esc_html__("Edit Userwise Private Content", 'ARMember');

	$member_private_content = get_user_meta( $member_id, 'arm_member_private_content', true );
	$member_data = get_userdata( $member_id );
	
	$member_login_name = $member_data->user_login . " (".$member_data->user_email.")";
	if(!empty($member_private_content)) {
		$member_private_content = json_decode($member_private_content);
		
		$private_content = stripslashes_deep(stripslashes_deep($member_private_content->private_content));
		$enable_private_content = (string)$member_private_content->enable_private_content;
	}
}


global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();
$user_table = $ARMember->tbl_arm_members;

$get_all_armembers = $wpdb->get_results("SELECT arm_member_id,arm_user_id,arm_user_login FROM {$user_table}", ARRAY_A);

?>

<div class="wrap arm_page arm_private_content_main_wrapper armPageContainer">
	<?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper arm_private_content_wrapper" id="content_wrapper">
		
		<div class="page_title"><?php echo $form_mode; ?></div>
        <div class="armclear"></div>

        <form  method="post" id="arm_add_edit_private_content_form" class="arm_add_edit_private_content_form arm_admin_form" onsubmit="return validate_form();">
        	<input type="hidden" name="id" id="arm_add_edit_private_content_id" value="<?php echo $member_id; ?>" />
            <input type="hidden" name="action" id="arm_private_content_action" value="<?php echo $action ?>" />
            <input type="hidden" name="enable_private_content" id="arm_private_content_status_input" value="<?php echo $enable_private_content ?>" />
            <div class="arm_admin_form_content">
            	<table class="form-table">
            		<tr class="form-field form-required">
            			<th>
                            <label for="user_name"><?php echo sprintf(esc_html__('Select User%ss%s', 'ARMember'), "(",")"); ?></label>
                        </th>
                        <td class="arm_required_member_wrapper">
                        	
                        	<?php 
                        		if($member_id != '') { ?>
                        			<strong><?php echo $member_login_name; ?></strong>
                        			<input type="hidden" name="arm_member_input_hidden[<?php echo $member_id; ?>]" value="<?php echo $member_id; ?>">
                        			
                        		<?php } else { ?>
                        		<input type="hidden" id="arm_member_item_type" class="arm_rule_item_type_input" name="arm_member_input_hidden" data-type="" value=""/>

                        		<input id="arm_member_items_input" type="text" value="" placeholder="<?php esc_html_e('Search by username or email...', 'ARMember');?>" data-msg-required="<?php esc_html_e('Please select atleast one member.', 'ARMember');?>">
								<div class="arm_private_content_items arm_required_wrapper arm_display_block" id="arm_private_content_items" style="display: none;"></div>

                        	<?php	}
                        	?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        	<label for="private_content"><?php esc_html_e('Private Content', 'ARMember'); ?></label>
                        </th>
                        <td>
                        	
                        	<div class="arm_private_content_editor arm_margin_bottom_25">
                        	<?php 
								$arm_message_editor = array('textarea_name' => 'arm_private_content',
									'editor_class' => 'arm_private_content',
									'media_buttons' => true,
									'textarea_rows' => 15,
									/*'default_editor' => 'html',*/
									'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>'
								);
								wp_editor($private_content, 'arm_private_content', $arm_message_editor);

							?>
							<span id="arm_comm_wp_validate_msg" class="error" style="display:none;"><?php esc_html_e('Content Cannot Be Empty.', 'ARMember');?></span>
							</div>
                        </td>
                    </tr>
                    
            	</table>


            	<div class="arm_submit_btn_container">
                    <button class="arm_save_btn" type="submit"><?php esc_html_e('Save', 'ARMember') ?></button>
                    <a class="arm_cancel_btn" href="<?php echo admin_url('admin.php?page=' . $arm_slugs->private_content); ?>"><?php esc_html_e('Close', 'ARMember'); ?></a>
                </div>
                <div class="armclear"></div>
            </div>
            <?php wp_nonce_field( 'arm_wp_nonce' );?>
     	</form>
        <div class="armclear"></div>
    </div>
</div>

<script type="text/javascript">

	function validate_form() {
		var action = jQuery("#arm_private_content_action").val();
		if(action != 'edit_private_content') {
			if(jQuery(".arm_private_content_items .arm_private_content_itembox").length > 0) {
				if(jQuery("#arm_private_content_items_input_error").length > 0) {
					jQuery("#arm_private_content_items_input_error").remove();
				}
				return true;
			} else {
				var msg = jQuery("#arm_member_items_input").attr('data-msg-required');
				var error_msg = '<span id="arm_private_content_items_input_error" class="error arm_invalid">'+msg+'</span>';
				jQuery(".arm_required_member_wrapper").append(error_msg);
				jQuery("#arm_member_items_input").focus();
				jQuery('html, body').animate({
	                scrollTop: jQuery("body").offset().top
	            }, 0);
				return false;
			}	
		}
	}

	jQuery(document).ready( function ($) {
		jQuery(document).on('click', '.arm_remove_selected_itembox', function () {
			jQuery(this).parents('.arm_private_content_itembox').remove();
			if(jQuery('#arm_private_content_items .arm_private_content_itembox').length == 0) {
				jQuery('#arm_member_items_input').attr('required', 'required');
				jQuery('#arm_private_content_items').hide();
			}
			return false;
		});


		if (jQuery.isFunction(jQuery().autocomplete))
		{
			if(jQuery("#arm_member_items_input").length > 0){
				jQuery('#arm_member_items_input').autocomplete({
					minLength: 0,
					delay: 500,
					appendTo: ".arm_private_content_main_wrapper",
					source: function (request, response) {
						var post_type = jQuery('#arm_member_item_type').val();
						var _wpnonce = jQuery('input[name="_wpnonce"]').val();
						jQuery.ajax({
							type: "POST",
							url: ajaxurl,
							dataType: 'json',
							data: "action=get_member_list&txt="+request.term + "&_wpnonce=" + _wpnonce,
							beforeSend: function () {},
							success: function (res) {
								response(res.data);
							}
						});
					},
					focus: function() {return false;},
					select: function(event, ui) {
						var itemData = ui.item;
						jQuery("#arm_member_items_input").val('');
						if(jQuery('#arm_private_content_items .arm_private_content_itembox_'+itemData.id).length > 0) {
						} else {
							var itemHtml = '<div class="arm_private_content_itembox arm_private_content_itembox_'+itemData.id+'">';
							itemHtml += '<input type="hidden" name="arm_member_input_hidden['+itemData.id+']" value="'+itemData.id+'"/>';
							itemHtml += '<label>'+itemData.label+'<span class="arm_remove_selected_itembox">x</span></label>';
							itemHtml += '</div>';
							jQuery("#arm_private_content_items").append(itemHtml);
							jQuery('#arm_member_items_input').removeAttr('required');
							if(jQuery("#arm_private_content_items_input_error").length > 0){
								jQuery("#arm_private_content_items_input_error").remove();
							}
						}
						jQuery('#arm_private_content_items').show();
						return false;
					},
				}).data('uiAutocomplete')._renderItem = function (ul, item) {
					var itemClass = 'ui-menu-item';
					if(jQuery('#arm_private_content_items .arm_private_content_itembox_'+item.id).length > 0) {
						itemClass += ' ui-menu-item-selected';
					}
					var itemHtml = '<li class="'+itemClass+'" data-value="'+item.value+'" data-id="'+item.id+'" ><a>' + item.label + '</a></li>';
					return jQuery(itemHtml).appendTo(ul);
				};
			}
		}
	});

</script>
<?php
	echo $ARMember->arm_get_need_help_html_content('users-private-content-add');
?>