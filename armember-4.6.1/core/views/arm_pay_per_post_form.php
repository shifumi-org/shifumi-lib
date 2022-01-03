<?php
$filter_search = (!empty($_POST['search'])) ? $_POST['search'] : '';
if (isset($_REQUEST['arm_default_paid_post_save'])) {
	do_action('arm_save_default_paid_post', $_REQUEST);
}

wp_enqueue_style('arm_post_metaboxes_css', MEMBERSHIP_URL . '/css/arm_post_metaboxes.css', array(), MEMBERSHIP_VERSION);
wp_enqueue_script('arm_tinymce', MEMBERSHIP_URL . '/js/arm_tinymce_member.js', array(), MEMBERSHIP_VERSION);

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();

global $wpdb, $ARMember, $arm_global_settings;
$user_table = $ARMember->tbl_arm_members;
$user_meta_table = $wpdb->usermeta;

$PaidPostContentTypes = array('page' => __('Page', 'ARMember'), 'post' => __('Post', 'ARMember'));
$custom_post_types = get_post_types(array('public' => true, '_builtin' => false, 'show_ui' => true), 'objects');
if (!empty($custom_post_types)) {
	foreach ($custom_post_types as $cpt) {
		$PaidPostContentTypes[$cpt->name] = $cpt->label;
	}
}

$action = isset( $_GET['action'] ) ? $_GET['action'] : 'add_paid_post';


$post_id = '';
$post_type = '';
$edit_paid_post = false;
if( 'edit_paid_post' == $action ){
	$post_id = isset( $_GET['post_id'] ) ? $_GET['post_id'] : '';
	$post_type = get_post_type( $post_id );
}

if( isset( $_GET['status'] ) && 'success' == $_GET['status'] ){
	echo "<script type='text/javascript'>";
		echo "jQuery(document).ready(function(){";
			echo "armToast('" . $_GET['msg'] . "','success');";
			echo "var pageurl = ArmRemoveVariableFromURL( document.URL, 'status' );";  
			echo "pageurl = ArmRemoveVariableFromURL( pageurl, 'msg' );";  
			echo "window.history.pushState( { path: pageurl }, '', pageurl );";
		echo "});";
	echo "</script>";
}

?>

<div class="wrap arm_page arm_paid_posts_main_wrapper">
	<div class="content_wrapper arm_paid_posts_wrapper" id="content_wrapper" style="position: relative;">
		<div class="page_title">
			<?php
				if( 'edit_paid_post' == $action ){
					esc_html_e('Edit Paid Posts','ARMember');
				} else {
					esc_html_e('Add Paid Posts','ARMember');
				}
			?>
			<?php
		    if ($setact != 1) {
		        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
	        ?>
	        <div style="margin-top:20px;margin-bottom:10px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARMember License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    		<?php } ?>
			
			<div class="armclear"></div>
		</div>
		<div class="armclear"></div>
		<?php
			global $arm_pay_per_post_feature;
			$total_paid_post_setups = $arm_pay_per_post_feature->arm_get_paid_post_setup();
			
			if( $total_paid_post_setups < 1 ){

				$arm_setup_link = admin_url( 'admin.php?page=arm_membership_setup&action=new_setup' );
		?>
			<div class="arm_admin_notices_container">
				<p><?php echo sprintf( esc_html__( 'You don\'t have created paid post type membership setup. Please create at least one membership setup for paid post from %s and then reload this page.', 'ARMember' ), '<a href="'.$arm_setup_link.'">here</a>' ) ?> </p>
			</div>
		<?php
			} else {
		?>
			<form method="post" id="arm_add_edit_paid_post_form" class="arm_add_edit_paid_post_form arm_admin_form" novalidate="novalidate">
				<?php
					if( 'edit_paid_post' == $action ){
						echo '<input type="hidden" name="edit_paid_post_id" value="' . $post_id . '" />';
						echo '<input type="hidden" name="edit_paid_post_type" value="' . $post_type . '" />';
					}
					echo '<input type="hidden" name="arm_action" value="arm_add_update_paid_post_plan" />';
					echo '<input type="hidden" name="action" value="' . $action . '" />';
				?>
				<div class="arm_admin_form_content postbox" id="arm_paid_post_metabox_wrapper">
					<table class="form-table">
						<tbody>
							<tr class="form-field form-required">
								<?php
									global $arm_pay_per_post_feature;
									if( 'edit_paid_post' == $_GET['action'] ){
										$postBlankObj = get_post( $post_id );
									} else {
										$postBlankObj = new stdClass();
									}
									$metabox_obj = array();
									$arm_pay_per_post_feature->arm_add_paid_post_metabox_html( $postBlankObj, $metabox_obj, true );
								?>
							</tr>
						</tbody>
					</table>
					<div class="arm_submit_btn_container">
						<button class="arm_save_btn" type="submit"><?php esc_html_e('Save','ARMember'); ?></button>
					</div>
				</div>
			</form>
		<?php
			}
		?>
		<div class="armclear"></div>
	</div>
</div>

<script type="text/javascript">

	jQuery(document).ready(function(){
		if( jQuery("#arm_paid_post_items_input").length > 0 ){
			arm_init_paid_post_autocomplete();
		}
	});

	function arm_init_paid_post_autocomplete(){
		if (jQuery.isFunction(jQuery().autocomplete)){
			jQuery('#arm_paid_post_items_input').autocomplete({
				minLength: 0,
				delay: 500,
				appendTo: "#arm_paid_post_items_list_container",
				source: function (request, response) {
					var post_type = jQuery('#arm_add_paid_post_item_type').val();
					var _wpnonce = jQuery('input[name="_wpnonce"]').val();
					jQuery.ajax({
						type: "POST",
						url: ajaxurl,
						dataType: 'json',
						data: "action=arm_get_paid_post_item_options&arm_post_type=" + post_type + "&search_key="+request.term + "&_wpnonce=" + _wpnonce,
						beforeSend: function () {},
						success: function (res) {
							response(res.data);
						}
					});
				},
				focus: function() {return false;},
				select: function(event, ui) {
					var itemData = ui.item;
					jQuery("#arm_paid_post_items_input").val('');
					if(jQuery('#arm_paid_post_items .arm_paid_post_itembox_'+itemData.id).length > 0) {
					} else {
						var itemHtml = '<div class="arm_paid_post_itembox arm_paid_post_itembox_'+itemData.id+'">';
						itemHtml += '<input type="hidden" name="arm_paid_post_item_id['+itemData.id+']" value="'+itemData.id+'"/>';
						itemHtml += '<label>'+itemData.label+'<span class="arm_remove_selected_itembox">x</span></label>';
						itemHtml += '</div>';
						jQuery("#arm_paid_post_items").append(itemHtml);
						jQuery('#arm_paid_post_items_input').removeAttr('required');
					}
					jQuery('#arm_paid_post_items').show();
					return false;
				},
			}).data('uiAutocomplete')._renderItem = function (ul, item) {
				var itemClass = 'ui-menu-item';
				if(jQuery('#arm_paid_post_items .arm_paid_post_itembox_'+item.id).length > 0) {
					itemClass += ' ui-menu-item-selected';
				}
				var itemHtml = '<li class="'+itemClass+'" data-value="'+item.value+'" data-id="'+item.id+'" ><a>' + item.label + '</a></li>';
				return jQuery(itemHtml).appendTo(ul);
			};
		}
	}

	jQuery(document).on('click', '.arm_remove_selected_itembox', function () {
		jQuery(this).parents('.arm_paid_post_itembox').remove();
		if(jQuery('#arm_paid_post_items .arm_paid_post_itembox').length == 0) {
			jQuery('#arm_paid_post_items_input').attr('required', 'required');
			jQuery('#arm_paid_post_items').hide();
		}
		return false;
	});	
</script>