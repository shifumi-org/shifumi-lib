<?php
/* start userbadge widget*/
if (!class_exists('arm_userbadge_widget')) {
	class arm_userbadge_widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				'arm_member_form_widget_user_badge',
				__('ARMember User Badge', 'ARMember'), 
				array( 'description' => __( 'Display User badges', 'ARMember' ), ) 
			);
		}
			 
		// Creating widget front-end
		public function widget ($args, $instance)
		{
			if (!is_user_logged_in()) return;
			
			$arm_title   = $instance['arm_title'];
			$arm_user_id = isset($instance['arm_user_id']) && !empty($instance['arm_user_id']) ? $instance['arm_user_id'] : '';
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }
			
			// Display user badges/achivements.
			echo do_shortcode('[arm_user_badge user_id="'.$arm_user_id.'"]');
			
			echo $args['after_widget'];
		}

		// Widget Backend
		public function form ($instance)
		{
			$arm_user_id='';
			if (isset($instance['arm_user_id'])) {
				$arm_user_id = $instance['arm_user_id'];
			}
			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			// Widget admin form ?>
			<p>
				<label for="<?php echo $this->get_field_id('arm_title'); ?>"><?php _e('Title:', 'ARMember'); ?></label>
				<input class="widefat" type="text" name="<?php echo $this->get_field_name('arm_title'); ?>" id="<?php echo $this->get_field_id('arm_title'); ?>" value="<?php echo esc_attr($arm_title); ?>" />&nbsp;
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('arm_user_id'); ?>"><?php _e('User ID:', 'ARMember'); ?></label>
				<input class="widefat" type="text" name="<?php echo $this->get_field_name('arm_user_id'); ?>" id="<?php echo $this->get_field_id('arm_user_id'); ?>" value="<?php echo esc_attr($arm_user_id); ?>" />
				<span class="arm_blank_field_note"><?php esc_html_e("If User ID is empty then by default current logged in user's badges will be displayed.", "ARMember") ?></span>
			</p>
			<?php
		}

		// Updating widget replacing old instances with new
		public function update ($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title']   = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : '';
			$instance['arm_user_id'] = !empty($new_instance['arm_user_id']) ? strip_tags($new_instance['arm_user_id']) : '';
			return $instance;
		}

	} // Class wpb_widget ends here

	// Register and load the widget
	if (class_exists('WP_Widget')) {
		function arm_register_userBadge_widgets() {
		    register_widget('arm_userbadge_widget');
		}
		add_action('widgets_init', 'arm_register_userBadge_widgets');
	}
}
/* end userbadge widget*/

/* start plan info widget*/
if (!class_exists('arm_plan_info_widget')) {
	class arm_plan_info_widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				'arm_member_form_widget_plan_info',
				__('ARMember Plan Info', 'ARMember'), 
				array(
					'description' => __('Display Plan Information of currently logged in user.', 'ARMember')
				)
			);
		}

		public function widget($args, $instance)
		{
			if (!is_user_logged_in()) return;
			
			$arm_title     = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : "";
			$arm_plan_id   = isset($instance['arm_plan_id']) && !empty($instance['arm_plan_id']) ? $instance['arm_plan_id'] : "";
			$arm_plan_info = isset($instance['arm_plan_info']) && !empty($instance['arm_plan_info']) ? $instance['arm_plan_info'] : "";
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }

			echo do_shortcode('[arm_user_planinfo plan_id="'.$arm_plan_id.'" plan_info="'.$arm_plan_info.'"]');

			echo $args['after_widget'];
		}

		public function form($instance)
		{
			global $arm_subscription_plans;
            $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('all');
			if (!empty($all_plans)) {
				$arm_title     = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
            	$arm_plan_id   = isset($instance['arm_plan_id']) && !empty($instance['arm_plan_id']) ? $instance['arm_plan_id'] : '';
            	$arm_plan_info = isset($instance['arm_plan_info']) && !empty($instance['arm_plan_info']) ? $instance['arm_plan_info'] : '';
            	?>
            	<p>
            		<label for="<?php echo $this->get_field_id('arm_title') ; ?>"><?php _e("Title", "ARMember"); ?></label>
            		<input type="text" class="widefat"  name="<?php echo $this->get_field_name('arm_title'); ?>" id="<?php echo $this->get_field_id('arm_title'); ?>" value="<?php echo esc_attr($arm_title); ?>" />&nbsp;
            	</p>
				<p>
					<label for="<?php echo $this->get_field_id('arm_plan_id'); ?>"><?php _e('Select Membership Plan', 'ARMember'); ?></label> 
					<select class="widefat" name="<?php echo $this->get_field_name('arm_plan_id'); ?>" id="<?php echo $this->get_field_id('arm_plan_id'); ?>">
						<option value="0">Select Membership Plan</option>
						<?php
						foreach ($all_plans as $plan) { ?>
							<option value="<?php echo $plan['arm_subscription_plan_id']; ?>" <?php echo $arm_plan_id == $plan['arm_subscription_plan_id'] ? "selected='selected'" : ''; ?> ><?php echo $plan['arm_subscription_plan_name']; ?></option>
						<?php } ?>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('arm_plan_info'); ?>"><?php _e('Select Plan Information', 'ARMember'); ?></label> 
					<select class="widefat" name="<?php echo $this->get_field_name('arm_plan_info'); ?>" id="<?php echo $this->get_field_id('arm_plan_info'); ?>">
						<option value="arm_start_plan" <?php echo $arm_plan_info == "arm_start_plan" ? "selected='selected'" : ''; ?> >Start Date</option>
					    <option value="arm_expire_plan" <?php echo $arm_plan_info == "arm_expire_plan" ? "selected='selected'" : ''; ?> >End Date</option>
					    <option value="arm_trial_start" <?php echo $arm_plan_info == "arm_trial_start" ? "selected='selected'" : ''; ?> >Trial Start Date</option>
					    <option value="arm_trial_end" <?php echo $arm_plan_info == "arm_trial_end" ? "selected='selected'" : ''; ?> >Trial End Date</option>
					    <option value="arm_grace_period_end" <?php echo $arm_plan_info == "arm_grace_period_end" ? "selected='selected'" : ''; ?> >Grace End Date</option>
					    <option value="arm_user_gateway" <?php echo $arm_plan_info == "arm_user_gateway" ? "selected='selected'" : ''; ?> >Paid By</option>
					    <option value="arm_completed_recurring" <?php echo $arm_plan_info == "arm_completed_recurring" ? "selected='selected'" : ''; ?> >Completed Recurrence</option>
					    <option value="arm_next_due_payment" <?php echo $arm_plan_info == "arm_next_due_payment" ? "selected='selected'" : ''; ?> >Next Due Date</option>
					    <option value="arm_payment_mode" <?php echo $arm_plan_info == "arm_payment_mode" ? "selected='selected'" : ''; ?> >Payment Mode</option>
					    <option value="arm_payment_cycle" <?php echo $arm_plan_info == "arm_payment_cycle" ? "selected='selected'" : ''; ?> >Payment Cycle</option>
					</select>
				</p>
			<?php }
		}

		// Updating widget replacing old instances with new
		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : '';
			$instance['arm_plan_id'] = !empty($new_instance['arm_plan_id']) ? $new_instance['arm_plan_id'] : '';
			$instance['arm_plan_info'] = !empty($new_instance['arm_plan_info']) ? $new_instance['arm_plan_info'] : '';
			return $instance;
		}
	}
	if (class_exists('WP_Widget')) {
		function arm_register_planInfo_widgets()
		{
			register_widget('arm_plan_info_widget');
		}
		add_action('widgets_init', 'arm_register_planInfo_widgets');
	}
}
/* end plan info widget*/

/* start user info widget*/
if (!class_exists('ARMUserInfoWidget')) {
	class ARMUserInfoWidget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				'arm_member_form_user_info_widget',
				__('ARMember User Info', 'ARMember'),
				array(
					'description' => __('Display currently logged in User Information', 'ARMember')
				)
			);
		}
		
		public function widget($args,$instance)
		{
			if (!is_user_logged_in()) return;
			
			$arm_title      = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_info_type  = isset($instance['arm_info_type']) && !empty($instance['arm_info_type']) ? $instance['arm_info_type'] : '';
			$arm_meta_field = isset($instance['arm_meta_field']) && !empty($instance['arm_meta_field']) ? sanitize_title($instance['arm_meta_field']) : '';
			if (empty($arm_info_type)) return;

            if ($arm_info_type == 'arm_usermeta' && empty(trim($arm_meta_field))) {
            	return;
            }

			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }

            if ($arm_info_type == 'arm_usermeta') {
            	if (empty(trim($arm_meta_field))) return;
            	echo do_shortcode('['.$arm_info_type.' meta="'.$arm_meta_field.'"]');
            } else {
				echo do_shortcode('['.$arm_info_type.']');
            }

			echo $args['after_widget'];
		}
		
		public function form($instance)
		{
			$arm_title     = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_info_type = isset($instance['arm_info_type']) && !empty($instance['arm_info_type']) ? $instance['arm_info_type'] : '';
			$arm_meta_field = isset($instance['arm_meta_field']) && !empty($instance['arm_meta_field']) ? $instance['arm_meta_field'] : '';
			?>
			<p>
        		<label for="<?php echo $this->get_field_id('arm_title') ; ?>"><?php _e("Title", "ARMember"); ?></label>
        		<input type="text" class="widefat"  name="<?php echo $this->get_field_name('arm_title'); ?>" id="<?php echo $this->get_field_id('arm_title'); ?>" value="<?php echo esc_attr($arm_title); ?>" />&nbsp;
        	</p>
        	<script type="text/javascript">
                function arm_show_widget_form_type(object){
                    var $this = jQuery(object);
                    var value = $this.val();
                    if( value == '' ){
                        return false;
                    }
                    if (value == 'arm_usermeta') {
                        jQuery('p#arm_meta_info_field').show();
                    } else {
                        jQuery('p#arm_meta_info_field').hide();
                    }
                }
            </script>
        	<p>
        		<select class="widefat" name="<?php echo $this->get_field_name('arm_info_type'); ?>" id="<?php echo $this->get_field_id('arm_info_type'); ?>" onChange="arm_show_widget_form_type(this);">
					<option value="0">Select Type</option>
					<option <?php echo $arm_info_type == "arm_userid" ? "selected='selected'" : ""; ?> value="arm_userid">User ID</option>
					<option <?php echo $arm_info_type == "arm_username" ? "selected='selected'" : ""; ?> value="arm_username">Username</option>
					<option <?php echo $arm_info_type == "arm_displayname" ? "selected='selected'" : ""; ?> value="arm_displayname">Display Name</option>
					<option <?php echo $arm_info_type == "arm_firstname_lastname" ? "selected='selected'" : ""; ?> value="arm_firstname_lastname">Firstname Lastname</option>
					<option <?php echo $arm_info_type == "arm_user_plan" ? "selected='selected'" : ""; ?> value="arm_user_plan">User Plan</option>
					<option <?php echo $arm_info_type == "arm_avatar" ? "selected='selected'" : ""; ?> value="arm_avatar">Avatar</option>
					<option <?php echo $arm_info_type == "arm_usermeta" ? "selected='selected'" : ""; ?> value="arm_usermeta">Custom Meta</option>
				</select>
        	</p>
        	<p id="arm_meta_info_field" style="<?php echo $arm_info_type == 'arm_usermeta' ? 'display:block;' : 'display:none;'; ?>">
        		<label for="<?php echo $this->get_field_name('arm_meta_field'); ?>"><?php _e("Usermeta Name", "ARMember"); ?></label>
        		<input class="widefat" type="text" name="<?php echo $this->get_field_name('arm_meta_field'); ?>" id="<?php echo $this->get_field_id('arm_meta_field'); ?>" value="<?php echo esc_attr($arm_meta_field);?>">
        	</p>
		<?php }
		
		public function update($new_instance,$old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : '';
			$instance['arm_info_type'] = !empty($new_instance['arm_info_type']) ? $new_instance['arm_info_type'] : '';
			$instance['arm_meta_field'] = !empty($new_instance['arm_meta_field']) ? $new_instance['arm_meta_field'] : '';
			return $instance;
		}
	}
	if (class_exists('WP_Widget')) {
		function arm_register_user_info_widgets()
		{
			register_widget('ARMUserInfoWidget');
		}
		add_action('widgets_init', 'arm_register_user_info_widgets');
	}
}
/* end user info widget*/

/* start logout widget*/
if (!class_exists('ARM_Logout_Widget')) {
	class ARM_Logout_Widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				"arm_member_form_widget_logout",
				__("ARMember Logout", "ARMember"),
				array(
					"description" => __("Display Logout button/link.", "ARMember")
				)
			);
		}

		public function widget($args, $instance)
		{
			if (!is_user_logged_in()) return;
			
			global $ARMember;
			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_link_type = isset($instance['arm_link_type']) && !empty($instance['arm_link_type']) ? $instance['arm_link_type'] : 'link';
			$arm_link_text = isset($instance['arm_link_text']) && !empty($instance['arm_link_text']) ? $instance['arm_link_text'] : __("Logout", "ARMember");
			$arm_dis_info = isset($instance['arm_dis_info']) && !empty($instance['arm_dis_info']) ? $instance['arm_dis_info'] : 'true';
			$arm_redirect_link = isset($instance['arm_redirect_link']) && !empty($instance['arm_redirect_link']) ? $instance['arm_redirect_link'] : ARM_HOME_URL;
			$arm_link_css = isset($instance['arm_link_css']) && !empty($instance['arm_link_css']) ? $instance['arm_link_css'] : '';
			$arm_link_hover_css = isset($instance['arm_link_hover_css']) && !empty($instance['arm_link_hover_css']) ? $instance['arm_link_hover_css'] : '';

			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }
            $ARMember->set_front_css(true);
			$ARMember->set_front_js(true);
			echo do_shortcode('[arm_logout type="'.$arm_link_type.'" label="'.$arm_link_text.'" user_info="'.$arm_dis_info.'" redirect_to="'.$arm_redirect_link.'" link_css="'.$arm_link_css.'" link_hover_css="'.$arm_link_hover_css.'"]');

			echo $args['after_widget'];
		}

		public function form($instance)
		{
			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_link_type = isset($instance['arm_link_type']) && !empty($instance['arm_link_type']) ? $instance['arm_link_type'] : 'link';
			$arm_link_text = isset($instance['arm_link_text']) && !empty($instance['arm_link_text']) ? $instance['arm_link_text'] : __("Logout", "ARMember");
			$arm_dis_info = isset($instance['arm_dis_info']) && !empty($instance['arm_dis_info']) ? $instance['arm_dis_info'] : 'true';
			$arm_redirect_link = isset($instance['arm_redirect_link']) && !empty($instance['arm_redirect_link']) ? $instance['arm_redirect_link'] : ARM_HOME_URL;
			$arm_link_css = isset($instance['arm_link_css']) && !empty($instance['arm_link_css']) ? $instance['arm_link_css'] : '';
			$arm_link_hover_css = isset($instance['arm_link_hover_css']) && !empty($instance['arm_link_hover_css']) ? $instance['arm_link_hover_css'] : '';
			?>
			<style type="text/css">
				.arm_hidden {
					display: none;
				}
			</style>
			<script type="text/javascript">
				function arm_update_form_field_label(object){
                    var $this = jQuery(object);
                    var value = $this.val();
                    if( value == '' ){
                        return false;
                    }
                    var button_label = "<?php _e('Button', 'ARMember'); ?>";
                    if (value == 'button') {
                        jQuery(".arm_shortcode_logout_button_opts").removeClass("arm_hidden");
                        jQuery(".arm_shortcode_logout_link_opts").addClass("arm_hidden");
                    } else {
                        jQuery(".arm_shortcode_logout_button_opts").addClass("arm_hidden");
                        jQuery(".arm_shortcode_logout_link_opts").removeClass("arm_hidden");
                    }
                }
			</script>
			<p>
				<label for="<?php echo $this->get_field_id('arm_title'); ?>"><?php _e("Title", "ARMember"); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('arm_title'); ?>" id="<?php echo $this->get_field_id('arm_title'); ?>" value="<?php echo esc_attr($arm_title); ?>" class="widefat">&nbsp;
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('arm_link_type'); ?>"><?php _e("Link Type", "ARMember"); ?></label>
				<select name="<?php echo $this->get_field_name('arm_link_type'); ?>" id="<?php echo $this->get_field_id('arm_link_type'); ?>" class="widefat" onChange="arm_update_form_field_label(this);">
					<option value="link" <?php echo $arm_link_type == "link" ? "selected='selected'" : ""; ?> ><?php _e("Link", "ARMember"); ?></option>
					<option value="button" <?php echo $arm_link_type == "button" ? "selected='selected'" : ""; ?> ><?php _e("Button", "ARMember"); ?></option>
				</select>
			</p>
			<p class="change_label">
				<label class="arm_shortcode_logout_link_opts <?php echo $arm_link_type == 'link' ? '' : 'arm_hidden'; ?>" for="<?php echo $this->get_field_id('arm_link_text'); ?>"><?php _e("Link Text", "ARMember"); ?></label>
				<label class="arm_shortcode_logout_button_opts <?php echo $arm_link_type == 'button' ? '' : 'arm_hidden'; ?>" for="<?php echo $this->get_field_id('arm_link_text'); ?>"><?php _e("Button Text", "ARMember"); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('arm_link_text'); ?>" id="<?php echo $this->get_field_id('arm_link_text'); ?>" value="<?php echo esc_attr($arm_link_text); ?>" class="widefat">
			</p>
			<p>
				<label><?php _e("Display User Info", "ARMember"); ?></label>
				<label><input type="radio" name="<?php echo $this->get_field_name('arm_dis_info'); ?>" id="<?php echo $this->get_field_id('arm_dis_info'); ?>" value="true" class="widefat" checked='checked' >Yes</label>
				<label><input type="radio" name="<?php echo $this->get_field_name('arm_dis_info'); ?>" id="<?php echo $this->get_field_id('arm_dis_info'); ?>" value="false" class="widefat" <?php echo $arm_dis_info == "false" ? "checked='checked'" : ""; ?> >No</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('arm_redirect_link'); ?>"><?php _e("Redirect After Logout", "ARMember"); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('arm_redirect_link'); ?>" id="<?php echo $this->get_field_id('arm_redirect_link'); ?>" value="<?php echo esc_attr($arm_redirect_link); ?>" class="widefat">
			</p>
			<p class="change_label">
				<label class="arm_shortcode_logout_link_opts <?php echo $arm_link_type == 'link' ? '' : 'arm_hidden'; ?>" for="<?php echo $this->get_field_id('arm_link_css'); ?>"><?php _e("Link CSS", "ARMember"); ?></label>
				<label class="arm_shortcode_logout_button_opts <?php echo $arm_link_type == 'button' ? '' : 'arm_hidden'; ?>" for="<?php echo $this->get_field_id('arm_link_css'); ?>"><?php _e("Button CSS", "ARMember"); ?></label>
				<textarea name="<?php echo $this->get_field_name('arm_link_css'); ?>" id="<?php echo $this->get_field_id('arm_link_css'); ?>" class="widefat"><?php echo esc_attr($arm_link_css); ?></textarea>
				<span>e.g. color: #000000;</span>
			</p>
			<p class="change_label">
				<label class="arm_shortcode_logout_link_opts <?php echo $arm_link_type == 'link' ? '' : 'arm_hidden'; ?>" for="<?php echo $this->get_field_id('arm_link_hover_css'); ?>"><?php _e("Link Hover CSS", "ARMember"); ?></label>
				<label class="arm_shortcode_logout_button_opts <?php echo $arm_link_type == 'button' ? '' : 'arm_hidden'; ?>" for="<?php echo $this->get_field_id('arm_link_hover_css'); ?>"><?php _e("Button Hover CSS", "ARMember"); ?></label>
				<textarea name="<?php echo $this->get_field_name('arm_link_hover_css'); ?>" id="<?php echo $this->get_field_id('arm_link_hover_css'); ?>" class="widefat"><?php echo esc_attr($arm_link_hover_css); ?></textarea>
				<span>e.g. color: #ffffff;</span>
			</p>
			<?php

		}

		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : '';
			$instance['arm_link_type'] = !empty($new_instance['arm_link_type']) ? $new_instance['arm_link_type'] : '';
			$instance['arm_link_text'] = !empty($new_instance['arm_link_text']) ? $new_instance['arm_link_text'] : '';
			$instance['arm_redirect_link'] = !empty($new_instance['arm_redirect_link']) ? strip_tags($new_instance['arm_redirect_link']) : '';
			$instance['arm_dis_info'] = !empty($new_instance['arm_dis_info']) ? $new_instance['arm_dis_info'] : '';
			$instance['arm_link_css'] = !empty($new_instance['arm_link_css']) ? strip_tags($new_instance['arm_link_css']) : '';
			$instance['arm_link_hover_css'] = !empty($new_instance['arm_link_hover_css']) ? strip_tags($new_instance['arm_link_hover_css']) : '';
			return $instance;
		}
	}
	if (class_exists('WP_Widget')) {
		function arm_register_Logout_widget()
		{
			register_widget('ARM_Logout_Widget');
		}
		add_action('widgets_init', 'arm_register_Logout_widget');
	}
}
/* end logout widget*/

/* start Social Login widget*/
if (!class_exists('ARM_Social_Login_Widget')) {
	class ARM_Social_Login_Widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				"arm_member_form_widget_social_login",
				__("ARMember Social Login", "ARMember"),
				array(
					"description" => __("Allow users to login through social media like Facebook, Twitter, LinkedIn, Google+, VK, Instagram, Tumblr.", "ARMember")
				)
			);
		}

		public function widget($args, $instance)
		{
			if (is_user_logged_in()) return;

			global $ARMember;
			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_social_network = isset($instance['arm_social_network']) && !empty($instance['arm_social_network']) ? $instance['arm_social_network'] : array();
			$arm_network_icon = isset($instance['arm_network_icon']) && !empty($instance['arm_network_icon']) ? $instance['arm_network_icon'] : array();

			if (empty($arm_social_network) || empty($arm_network_icon)) return;

			$ARMember->set_front_css(true);
			$ARMember->set_front_js(true);
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }
            if (count($arm_social_network) > 1) {
            	foreach ($arm_social_network as $social_network) {
            		echo do_shortcode('[arm_social_login network="'.$social_network.'" icon="'.$arm_network_icon[$social_network].'"]');
            	}
            } else {
            	echo do_shortcode('[arm_social_login network="'.$arm_social_network[0].'" icon="'.$arm_network_icon[$arm_social_network[0]].'"]');
            }

			echo $args['after_widget'];
		}

		public function form($instance)
		{
			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_social_network = isset($instance['arm_social_network']) && !empty($instance['arm_social_network']) ? $instance['arm_social_network'] : array();
			$arm_network_icon = isset($instance['arm_network_icon']) && !empty($instance['arm_network_icon']) ? $instance['arm_network_icon'] : array();

			global $arm_social_feature;
			$social_options = $arm_social_feature->arm_get_active_social_options();

			if (empty($social_options)) _e("Please Enable social connections from the ARMember > General Settings > Social Connect", 'ARMember');
			else {
	            ?>
	            <script type="text/javascript">
	            	function arm_update_form_social_icons(object)
	            	{
	            		var $this = jQuery(object);
	                    var value = $this.val();
	                    if( value == '' ) {
	                        return false;
	                    }
	                    if (jQuery($this).is(":checked")) {
	                    	jQuery(".arm_social_login_"+value+"_icons").removeClass("arm_hidden");
	                    	jQuery(".arm_social_login_icons.arm_social_login_"+value+"_icons ."+value+"_icon1").prop("checked", true);
	                    } else {
	                    	jQuery(".arm_social_login_"+value+"_icons").addClass("arm_hidden");
	                    	jQuery(".arm_social_login_icons.arm_social_login_"+value+"_icons ."+value+"_icon1").prop("checked", false);
	                    }
	            	}
	            </script>

	            <style type="text/css">
	            	.arm_hidden {
	            		display: none;
	            	}
	            	.arm_social_login_icon_container {
					    display: block;
					    vertical-align: middle;
					    margin-bottom: 5px;
					    margin-top: 5px;
					}
					.arm_span {
					    width: 100%;
					    display: block;
					    margin-bottom: 8px;
					}
					label.arm_social_network {
					    display: inline-block;
					    width: auto;
					    margin: 5px 0;
					    min-width: 30%;
					    text-transform: capitalize;
					}
	            	input.radio {
					    vertical-align: middle;
					    margin: 0 !important;
					    line-height: 0px !important;
					}
					.arm_social_login_icon_container img {
					    display: inline-block;
					    vertical-align: middle;
					}
	            </style>

				<p>
					<label for="<?php echo $this->get_field_id('arm_title'); ?>"><?php _e("Title", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_title'); ?>" id="<?php echo $this->get_field_id('arm_title'); ?>" class="widefat" value="<?php echo esc_attr($arm_title); ?>">&nbsp;
				</p>

				<p>
					<span class="arm_span"><?php _e("Network Type", "ARMember"); ?></span>
					<?php
					foreach ($social_options as $sk => $so) {
						?>
						<label class="arm_social_network">
							<input type="checkbox" onchange="arm_update_form_social_icons(this)" name="<?php echo $this->get_field_name('arm_social_network'); ?>[]" value="<?php echo $sk ?>" class="arm_checkbox" <?php echo in_array($sk, $arm_social_network) ? "checked='checked'" : ""; ?> ><?php echo $sk; ?>
						</label>
	            	<?php } ?>
				</p>

				<?php foreach ($social_options as $sk => $so) { ?>
					<div class="arm_social_login_icons arm_social_login_<?php echo $sk; ?>_icons <?php echo !in_array($sk, $arm_social_network) ? 'arm_hidden' : ''; ?>">
						<label for="<?php echo $this->get_field_id('arm_network_icon'); ?>"><?php _e("Network Icon", "ARMember"); ?></label>
						<?php
						$icons = $arm_social_feature->arm_get_social_network_icons($sk);
						$i=0;
						if (!empty($icons)) {
							foreach($icons as $icon => $url) {
								$i++;
								?>
								<div class="arm_social_login_icon_container">
									<input type="radio" class="radio <?php echo $sk; ?>_icon<?php echo $i; ?>" name="<?php echo $this->get_field_name('arm_network_icon'); ?>[<?php echo $sk; ?>]" value="<?php echo $url;?>" <?php 
										if(isset($arm_network_icon[$sk]) && $arm_network_icon[$sk] == $url) 
										{
											echo "checked='checked'";
										}
										elseif($i==1 && $arm_social_network==$sk) { 
											echo "checked='checked'"; 
										} ?> >
									<?php
									if (file_exists(strstr($url, "//"))) {
			                            $url_icon =strstr($url, "//");
			                        } else if (file_exists($url)) {
			                           $url_icon = $url;
			                        } else {
			                            $url_icon = $url;
			                        }
			                        ?>
			                        <img src="<?php echo ($url_icon);?>" alt="<?php echo $so['label']; ?>"/>
			                    </div>
		                        <?php
							}
						} ?>
					</div>
				<?php }
			}
		}

		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : '';
			$instance['arm_social_network'] = !empty($new_instance['arm_social_network']) ? $new_instance['arm_social_network'] : '';
			$instance['arm_network_icon'] = !empty($new_instance['arm_network_icon']) ? $new_instance['arm_network_icon'] : '';
			return $instance;
		}
	}

	if (class_exists('WP_Widget')) {
		function arm_register_Social_Login_widget()
		{
			register_widget("ARM_Social_Login_Widget");	
		}
		global $arm_social_feature;
		if(!empty($arm_social_feature) && isset($arm_social_feature->isSocialLoginFeature) && $arm_social_feature->isSocialLoginFeature == 1) {
			$social_options = $arm_social_feature->arm_get_active_social_options();
			if( !empty($social_options) )
			{
				add_action("widgets_init", "arm_register_Social_Login_widget");
			}
		}
	}
}
/* end Social Login widget*/

/* start User Profile widget*/
if (!class_exists('ARM_User_Profile_Widget')) {
	class ARM_User_Profile_Widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				"arm_member_form_widget_user_profile",
				__("ARMember User Profile", "ARMember"),
				array(
					"description" => __("Display Currently logged in user's profile.", "ARMember")
				)
			);
		}

		public function widget($args, $instance)
		{
			if (!is_user_logged_in()) return;

			global $ARMember;
			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : "";
			$arm_profile_fields = isset($instance['arm_profile_fields']) && !empty($instance['arm_profile_fields']) ? $instance['arm_profile_fields'] : "";
			$arm_profile_fields_label = isset($instance['arm_profile_fields_label']) && !empty($instance['arm_profile_fields_label']) ? $instance['arm_profile_fields_label'] : "";
			$arm_social_fields = isset($instance['arm_social_fields']) && !empty($instance['arm_social_fields']) ? implode(",", $instance['arm_social_fields']) : "";

			if (empty($arm_profile_fields) || empty($arm_profile_fields_label)) return;

			foreach ($arm_profile_fields_label as $key => $value) {
				if (!in_array($key, $arm_profile_fields)) {
					unset($arm_profile_fields_label[$key]);
				}
			}
			$ARMember->set_front_css(true);
			$ARMember->set_front_js(true);
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }
			
			echo do_shortcode('[arm_account_detail social_fields="'.$arm_social_fields.'" label="'.implode(",", $arm_profile_fields).'" value="'.implode(",", $arm_profile_fields_label).'"]');

			echo $args['after_widget'];
		}

		public function form($instance)
		{
			$arm_title                = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_profile_fields       = isset($instance['arm_profile_fields']) && !empty($instance['arm_profile_fields']) ? $instance['arm_profile_fields'] : array('first_name','last_name','display_name','user_login','user_email','gender','user_url','country','description');

			$arm_profile_fields_label = isset($instance['arm_profile_fields_label']) && !empty($instance['arm_profile_fields_label']) ? $instance['arm_profile_fields_label'] : array();
			$arm_social_fields        = isset($instance['arm_social_fields']) && !empty($instance['arm_social_fields']) ? $instance['arm_social_fields'] : array();

			$first_name = isset($arm_profile_fields_label['first_name']) && !empty($arm_profile_fields_label['first_name']) ? $arm_profile_fields_label['first_name'] : __("First Name", "ARMember");
			$last_name = isset($arm_profile_fields_label['last_name']) && !empty($arm_profile_fields_label['last_name']) ? $arm_profile_fields_label['last_name'] : __("Last Name", "ARMember");
			$display_name = isset($arm_profile_fields_label['display_name']) && !empty($arm_profile_fields_label['display_name']) ? $arm_profile_fields_label['display_name'] : __("Display Profile Name", "ARMember");
			$user_login = isset($arm_profile_fields_label['user_login']) && !empty($arm_profile_fields_label['user_login']) ? $arm_profile_fields_label['user_login'] : __("Username", "ARMember");
			$user_email = isset($arm_profile_fields_label['user_email']) && !empty($arm_profile_fields_label['user_email']) ? $arm_profile_fields_label['user_email'] : __("Email Address", "ARMember");
			$gender = isset($arm_profile_fields_label['gender']) && !empty($arm_profile_fields_label['gender']) ? $arm_profile_fields_label['gender'] : __("Gender", "ARMember");
			$user_url = isset($arm_profile_fields_label['user_url']) && !empty($arm_profile_fields_label['user_url']) ? $arm_profile_fields_label['user_url'] : __("Website Url", "ARMember");
			$country = isset($arm_profile_fields_label['country']) && !empty($arm_profile_fields_label['country']) ? $arm_profile_fields_label['country'] : __("Country/Region", "ARMember");
			$description = isset($arm_profile_fields_label['description']) && !empty($arm_profile_fields_label['description']) ? $arm_profile_fields_label['description'] : __("Biography", "ARMember");
			?>

			<style type="text/css">
				.arm_profile_fields {
				    display: block;
    				margin: 5px 0px 5px 5px;
				}
				.arm_checkbox {
				    display: inline-block;
				    width: 30%;
				    margin: 5px;
				}
				.arm_profile_fields input[type="text"] {
				    width: 93%;
				    margin : 0px;
				}
			</style>

			<p>
				<label for="<?php echo $this->get_field_id('arm_title'); ?>"><?php _e("Title", "ARMember"); ?></label>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name('arm_title'); ?>" id="<?php echo $this->get_field_id('arm_title'); ?>" value="<?php echo esc_attr($arm_title); ?>">
			</p>

			<p>
				<span class="arm_span"><?php _e("Profile Fields", "ARMember"); ?></span>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="first_name" <?php echo in_array('first_name', $arm_profile_fields) ? "checked='checked'" : "" ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[first_name]" value="<?php echo esc_attr($first_name); ?>">
				</div>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="last_name" <?php echo in_array('last_name', $arm_profile_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[last_name]" value="<?php echo esc_attr($last_name); ?>">
				</div>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="display_name" <?php echo in_array('display_name', $arm_profile_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[display_name]" value="<?php echo esc_attr($display_name); ?>">
				</div>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="user_login" <?php echo in_array('user_login', $arm_profile_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[user_login]" value="<?php echo esc_attr($user_login); ?>">
				</div>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="user_email" <?php echo in_array('user_email', $arm_profile_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[user_email]" value="<?php echo esc_attr($user_email); ?>">
				</div>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="gender" <?php echo in_array('gender', $arm_profile_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[gender]" value="<?php echo esc_attr($gender); ?>">
				</div>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="user_url" <?php echo in_array('user_url', $arm_profile_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[user_url]" value="<?php echo esc_attr($user_url); ?>">
				</div>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="country" <?php echo in_array('country', $arm_profile_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[country]" value="<?php echo esc_attr($country); ?>">
				</div>
				<div class="arm_profile_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_profile_fields'); ?>[]" value="description" <?php echo in_array('description', $arm_profile_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_profile_fields_label'); ?>[description]" value="<?php echo esc_attr($description); ?>">
				</div>
			</p>

			<p>
				<span class="arm_span"><?php _e("Social Profile Fields", "ARMember"); ?></span>
			</p>
			<p>	
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="facebook" <?php echo in_array("facebook", $arm_social_fields) ? "checked='checked'" : "" ?>  ><?php _e("Facebook", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="twitter" <?php echo in_array("twitter", $arm_social_fields) ? "checked='checked'" : "" ?>  ><?php _e("Twitter", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="linkedin" <?php echo in_array("linkedin", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("LinkedIn", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="vk" <?php echo in_array("vk", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("VK", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="instagram" <?php echo in_array("instagram", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Instagram", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="pinterest" <?php echo in_array("pinterest", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Pinterest", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="youtube" <?php echo in_array("youtube", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Youtube", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="dribbble" <?php echo in_array("dribbble", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Dribbble", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="delicious" <?php echo in_array("delicious", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Delicious", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="tumblr" <?php echo in_array("tumblr", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Tumblr", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="vine" <?php echo in_array("vine", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Vine", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="skype" <?php echo in_array("skype", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Skype", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="whatsapp" <?php echo in_array("whatsapp", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("WhatsApp", "ARMember"); ?>
				</label>
				<label class="arm_checkbox">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_social_fields'); ?>[]" value="tiktok" <?php echo in_array("tiktok", $arm_social_fields) ? "checked='checked'" : "" ?> ><?php _e("Tiktok", "ARMember"); ?>
				</label>

			</p>
			<?php

		}

		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : '';
			$instance['arm_profile_fields'] = !empty($new_instance['arm_profile_fields']) ? $new_instance['arm_profile_fields'] : '';
			$instance['arm_profile_fields_label'] = !empty($new_instance['arm_profile_fields_label']) ? $new_instance['arm_profile_fields_label'] : '';
			$instance['arm_social_fields'] = !empty($new_instance['arm_social_fields']) ? $new_instance['arm_social_fields'] : '';
			return $instance;
		}
	}

	if (class_exists('WP_Widget')) {
		function arm_register_profile_widget()
		{
			register_widget("ARM_User_Profile_Widget");
		}
		add_action("widgets_init", "arm_register_profile_widget");
	}
}
/* end User Profile widget*/

/* start User Account Close widget*/
if (!class_exists('ARM_User_Account_Close_Widget')) {
	class ARM_User_Account_Close_Widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				"arm_member_form_widget_user_close_account",
				__("ARMember Close Account", "ARMember"),
				array(
					"description"  => __("Close/Delete account. This will erase all of the data of current logged in user from site.This form will not display to if current user is Admin.", "ARMember")
				)
			);
		}

		public function widget($args, $instance)
		{
			if (!is_user_logged_in()) return;

			global $ARMember;
			$arm_title      = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_login_form = isset($instance['arm_login_form']) && !empty($instance['arm_login_form']) ? $instance['arm_login_form'] : '';
			$arm_css        = isset($instance['arm_css']) && !empty($instance['arm_css']) ? $instance['arm_css'] : '';
			
			if (empty($arm_login_form)) return;

			$ARMember->set_front_css(true);
			$ARMember->set_front_js(true);
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }
			
			echo do_shortcode('[arm_close_account set_id="'.$arm_login_form.'" css="'.$arm_css.'"]');

			echo $args['after_widget'];
		}

		public function form($instance)
		{
			$arm_title      = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : '';
			$arm_login_form = isset($instance['arm_login_form']) && !empty($instance['arm_login_form']) ? $instance['arm_login_form'] : '';
			$arm_css        = isset($instance['arm_css']) && !empty($instance['arm_css']) ? $instance['arm_css'] : '';

			global $wpdb, $ARMember;
			$login_forms = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type` = 'login' GROUP BY arm_set_id ORDER BY arm_form_id ASC");
			if (empty($login_forms)) {
				_e("We didn't founds any Login related forms.. Please create this from ARMember > Manage Forms > Other Forms (Login / Forgot Password / Change Password)", "");
			} else { ?>
				<p>
					<label for="<?php echo $this->get_field_id('arm_title'); ?>"><?php _e("Title", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_title'); ?>" value="<?php echo esc_attr($arm_title); ?>" class="widefat">&nbsp;
				</p>

				<p>
					<label for="<?php echo $this->get_field_id('arm_login_form'); ?>"><?php _e("Select Set of Login Form", "ARMember"); ?></label>
					<select name="<?php echo $this->get_field_name('arm_login_form'); ?>" id="<?php echo $this->get_field_id('arm_login_form'); ?>" class="widefat">
						<?php foreach ($login_forms as $forms) { ?>
							<option value="<?php echo $forms->arm_form_id ?>" <?php echo $arm_login_form == $forms->arm_form_id ? "selected='selected'" : ""; ?> ><?php echo $forms->arm_set_name; ?></option>
						<?php } ?>
					</select>
				</p>

				<p>
					<label for="<?php echo $this->get_field_id('arm_css'); ?>"><?php _e("Custom Css", "ARMember"); ?></label>
					<textarea class="widefat" name="<?php echo $this->get_field_name('arm_css'); ?>" id="<?php echo $this->get_field_id('arm_css'); ?>"><?php echo esc_attr($arm_css); ?></textarea>
					<span>e.g. .classname { color: #ffffff;}</span>
				</p>
			<?php }
		}

		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : '';
			$instance['arm_login_form'] = !empty($new_instance['arm_login_form']) ? $new_instance['arm_login_form'] : '';
			$instance['arm_css'] = !empty($new_instance['arm_css']) ? strip_tags($new_instance['arm_css']) : '';
			return $instance;
		}
	}
	if (class_exists('WP_Widget')) {
		function arm_register_user_delete_account_widget()
		{
			register_widget("ARM_User_Account_Close_Widget");
		}
		add_action("widgets_init", "arm_register_user_delete_account_widget");
	}
}
/* end User Account Close widget*/

/* start Payment Transaction widget*/
if (!class_exists('ARM_Payment_Transaction_Widget')) {
	class ARM_Payment_Transaction_Widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				"arm_member_widget_payment_transaction",
				__("ARMember Payment Transaction", "ARMember"),
				array(
					"description"  => __("ARMember Payment Transaction", "ARMember")
				)
			);
		}

		public function widget($args, $instance)
		{
			if (!is_user_logged_in()) return;

			global $ARMember;

			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : __("Transactions", "ARMember");
			$arm_transaction_fields = isset($instance['arm_transaction_fields']) && !empty($instance['arm_transaction_fields']) ? $instance['arm_transaction_fields'] : array();
			$arm_transaction_fields_label = isset($instance['arm_transaction_fields_label']) && !empty($instance['arm_transaction_fields_label']) ? $instance['arm_transaction_fields_label'] : array();
			$arm_dis_invoice_btn = isset($instance['arm_dis_invoice_btn']) && !empty($instance['arm_dis_invoice_btn']) ? $instance['arm_dis_invoice_btn'] : "false";
			$arm_invoice_btn_text = isset($instance['arm_invoice_btn_text']) && !empty($instance['arm_invoice_btn_text']) ? $instance['arm_invoice_btn_text'] : __("View Invoice", "ARMember");
			$arm_invoice_css = isset($instance['arm_invoice_css']) && !empty($instance['arm_invoice_css']) ? $instance['arm_invoice_css'] : '';
			$arm_invoice_hover_css = isset($instance['arm_invoice_hover_css']) && !empty($instance['arm_invoice_hover_css']) ? $instance['arm_invoice_hover_css'] : '';
			$arm_per_page = isset($instance['arm_per_page']) && !empty($instance['arm_per_page']) ? $instance['arm_per_page'] : '5';
			$arm_no_record_msg = isset($instance['arm_no_record_msg']) && !empty($instance['arm_no_record_msg']) ? $instance['arm_no_record_msg'] : __("There is no any Transactions found", "ARMember");

			if (empty($arm_transaction_fields) || empty($arm_transaction_fields_label)) return;

			foreach ($arm_transaction_fields_label as $key => $value) {
				if (!in_array($key, $arm_transaction_fields)) {
					unset($arm_transaction_fields_label[$key]);
				}
			}
			$ARMember->set_front_css(true);
			$ARMember->set_front_js(true);
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }

            echo do_shortcode('[arm_member_transaction display_invoice_button="'.$arm_dis_invoice_btn.'" view_invoice_text="'.$arm_invoice_btn_text.'" view_invoice_css="'.$arm_invoice_css.'" view_invoice_hover_css="'.$arm_invoice_hover_css.'" title="'.$arm_title.'" per_page="'.$arm_per_page.'" message_no_record="'.$arm_no_record_msg.'" label="'.implode(",", $arm_transaction_fields).'" value="'.implode(",", $arm_transaction_fields_label).'"]');
			
			echo $args['after_widget'];
		}

		public function form($instance)
		{
			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : __("Transactions", "ARMember");
			$arm_transaction_fields = isset($instance['arm_transaction_fields']) && !empty($instance['arm_transaction_fields']) ? $instance['arm_transaction_fields'] : array('transaction_id','invoice_id','plan','payment_gateway','payment_type','transaction_status','amount','used_coupon_code','used_coupon_discount','payment_date','tax_percentage','tax_amount');
			
			$arm_transaction_fields_label = isset($instance['arm_transaction_fields_label']) && !empty($instance['arm_transaction_fields_label']) ? $instance['arm_transaction_fields_label'] : array();
			$arm_dis_invoice_btn = isset($instance['arm_dis_invoice_btn']) && !empty($instance['arm_dis_invoice_btn']) ? $instance['arm_dis_invoice_btn'] : "false";
			$arm_invoice_btn_text = isset($instance['arm_invoice_btn_text']) && !empty($instance['arm_invoice_btn_text']) ? $instance['arm_invoice_btn_text'] : __("View Invoice", "ARMember");
			$arm_invoice_css = isset($instance['arm_invoice_css']) && !empty($instance['arm_invoice_css']) ? $instance['arm_invoice_css'] : '';
			$arm_invoice_hover_css = isset($instance['arm_invoice_hover_css']) && !empty($instance['arm_invoice_hover_css']) ? $instance['arm_invoice_hover_css'] : '';
			// $title = isset($instance['title']) && !empty($instance['title']) ? $instance['title'] : '';
			$arm_per_page = isset($instance['arm_per_page']) && !empty($instance['arm_per_page']) ? $instance['arm_per_page'] : '5';
			$arm_no_record_msg = isset($instance['arm_no_record_msg']) && !empty($instance['arm_no_record_msg']) ? $instance['arm_no_record_msg'] : __("There is no any Transactions found", "ARMember");


			$transaction_id = isset($arm_transaction_fields_label['transaction_id']) && !empty($arm_transaction_fields_label['transaction_id']) ? $arm_transaction_fields_label['transaction_id'] : __("Transaction ID", "ARMember");
			$invoice_id = isset($arm_transaction_fields_label['invoice_id']) && !empty($arm_transaction_fields_label['invoice_id']) ? $arm_transaction_fields_label['invoice_id'] : __("Invoice ID", "ARMember");
			$plan = isset($arm_transaction_fields_label['plan']) && !empty($arm_transaction_fields_label['plan']) ? $arm_transaction_fields_label['plan'] : __("Plan", "ARMember");
			$payment_gateway = isset($arm_transaction_fields_label['payment_gateway']) && !empty($arm_transaction_fields_label['payment_gateway']) ? $arm_transaction_fields_label['payment_gateway'] : __("Payment Gateway", "ARMember");
			$payment_type = isset($arm_transaction_fields_label['payment_type']) && !empty($arm_transaction_fields_label['payment_type']) ? $arm_transaction_fields_label['payment_type'] : __("Payment Type", "ARMember");
			$transaction_status = isset($arm_transaction_fields_label['transaction_status']) && !empty($arm_transaction_fields_label['transaction_status']) ? $arm_transaction_fields_label['transaction_status'] : __("Transaction Status", "ARMember");
			$amount = isset($arm_transaction_fields_label['amount']) && !empty($arm_transaction_fields_label['amount']) ? $arm_transaction_fields_label['amount'] : __("Amount", "ARMember");
			$used_coupon_code = isset($arm_transaction_fields_label['used_coupon_code']) && !empty($arm_transaction_fields_label['used_coupon_code']) ? $arm_transaction_fields_label['used_coupon_code'] : __("Used coupon Code", "ARMember");
			$used_coupon_discount = isset($arm_transaction_fields_label['used_coupon_discount']) && !empty($arm_transaction_fields_label['used_coupon_discount']) ? $arm_transaction_fields_label['used_coupon_discount'] : __("Used coupon Discount", "ARMember");
			$payment_date = isset($arm_transaction_fields_label['payment_date']) && !empty($arm_transaction_fields_label['payment_date']) ? $arm_transaction_fields_label['payment_date'] : __("Payment Date", "ARMember");
			$tax_percentage = isset($arm_transaction_fields_label['tax_percentage']) && !empty($arm_transaction_fields_label['tax_percentage']) ? $arm_transaction_fields_label['tax_percentage'] : __("TAX Percentage", "ARMember");
			$tax_amount = isset($arm_transaction_fields_label['tax_amount']) && !empty($arm_transaction_fields_label['tax_amount']) ? $arm_transaction_fields_label['tax_amount'] : __("TAX Amount", "ARMember");

			?>

			<style type="text/css">
				.arm_transaction_fields {
				    display: block;
    				margin: 5px 0px 5px 5px;
				}
				.arm_checkbox {
				    display: inline-block;
				    width: 30%;
				    margin: 5px;
				}
				.arm_transaction_fields input[type="text"] {
				    width: 93%;
				    margin : 0px;
				}
			</style>

			<script type="text/javascript">
				function arm_update_invoince_fields(object)
				{
					var $this = jQuery(object);
					var value = $this.val();
					if (value == '') {
						return false;
					}
					if (value == "true") {
						jQuery(".invoice_fields").removeClass("arm_hidden");
					} else {
						jQuery(".invoice_fields").addClass("arm_hidden");
					}
				}
			</script>

			<p>
				<label for="<?php echo $this->get_field_id('arm_title'); ?>"><?php _e("Title", "ARMember"); ?> </label>
				<input type="text" name="<?php echo $this->get_field_name('arm_title'); ?>" id="<?php echo $this->get_field_id('arm_title'); ?>" value="<?php echo esc_attr($arm_title); ?>" class="widefat">&nbsp;
			</p>

			<p>
				<span class="arm_span"><?php _e("Transaction History", "ARMember"); ?></span>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="transaction_id" <?php echo in_array('transaction_id', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[transaction_id]" value="<?php echo esc_attr($transaction_id); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="invoice_id" <?php echo in_array('invoice_id', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[invoice_id]" value="<?php echo esc_attr($invoice_id); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="plan" <?php echo in_array('plan', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[plan]" value="<?php echo esc_attr($plan); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="payment_gateway" <?php echo in_array('payment_gateway', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[payment_gateway]" value="<?php echo esc_attr($payment_gateway); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="payment_type" <?php echo in_array('payment_type', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[payment_type]" value="<?php echo esc_attr($payment_type); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="transaction_status" <?php echo in_array('transaction_status', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[transaction_status]" value="<?php echo esc_attr($transaction_status); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="amount" <?php echo in_array('amount', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[amount]" value="<?php echo esc_attr($amount); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="used_coupon_code" <?php echo in_array('used_coupon_code', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[used_coupon_code]" value="<?php echo esc_attr($used_coupon_code); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="used_coupon_discount" <?php echo in_array('used_coupon_discount', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[used_coupon_discount]" value="<?php echo esc_attr($used_coupon_discount); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="payment_date" <?php echo in_array('payment_date', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[payment_date]" value="<?php echo esc_attr($payment_date); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="tax_percentage" <?php echo in_array('tax_percentage', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[tax_percentage]" value="<?php echo esc_attr($tax_percentage); ?>">
				</div>
				<div class="arm_transaction_fields">
					<input type="checkbox" name="<?php echo $this->get_field_name('arm_transaction_fields'); ?>[]" value="tax_amount" <?php echo in_array('tax_amount', $arm_transaction_fields) ? "checked='checked'" : ""; ?> >
					<input type="text" name="<?php echo $this->get_field_name('arm_transaction_fields_label'); ?>[tax_amount]" value="<?php echo esc_attr($tax_amount); ?>">
				</div>
			</p>

			<p>
				<span class="arm_span"><?php _e("Display View Invoice Button?", "ARMember"); ?></span>
				<label>
					<input type="radio" <?php echo $arm_dis_invoice_btn == "false" ? "checked='checked'" : ""; ?> onChange="arm_update_invoince_fields(this)" name="<?php echo $this->get_field_name('arm_dis_invoice_btn'); ?>" value="false">No
				</label>
				<label>
					<input type="radio" <?php echo $arm_dis_invoice_btn == "true" ? "checked='checked'" : ""; ?> onChange="arm_update_invoince_fields(this)" name="<?php echo $this->get_field_name('arm_dis_invoice_btn'); ?>" value="true">Yes
				</label>
			</p>

			<p class="invoice_fields <?php echo $arm_dis_invoice_btn == 'false' ? 'arm_hidden' : ''; ?>">
				<label><?php _e("View Invoice Text", "ARMember"); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('arm_invoice_btn_text'); ?>" id="<?php echo $this->get_field_id('arm_invoice_btn_text'); ?>" class="widefat" value="<?php echo esc_attr($arm_invoice_btn_text); ?>">
			</p>

			<p class="invoice_fields <?php echo $arm_dis_invoice_btn == 'false' ? 'arm_hidden' : ''; ?>">
				<label><?php _e("Button CSS", "ARMember"); ?></label>
				<textarea  name="<?php echo $this->get_field_name('arm_invoice_css'); ?>" id="<?php echo $this->get_field_id('arm_invoice_css'); ?>" class="widefat"><?php echo esc_attr($arm_invoice_css); ?></textarea>
				<span>e.g. color: #ffffff;</span>
			</p>

			<p class="invoice_fields <?php echo $arm_dis_invoice_btn == 'false' ? 'arm_hidden' : ''; ?>">
				<label><?php _e("Button Hover CSS", "ARMember"); ?></label>
				<textarea  name="<?php echo $this->get_field_name('arm_invoice_hover_css'); ?>" id="<?php echo $this->get_field_id('arm_invoice_hover_css'); ?>" class="widefat"><?php echo esc_attr($arm_invoice_hover_css); ?></textarea>
				<span>e.g. color: #ffffff;</span>
			</p>

			
			<p>
				<label><?php _e("Records Per Page", "ARMember"); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('arm_per_page'); ?>" id="<?php echo $this->get_field_id('arm_per_page'); ?>" class="widefat" value="<?php echo esc_attr($arm_per_page); ?>">
			</p>

			<p>
				<label><?php _e("No Records Message", "ARMember"); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('arm_no_record_msg'); ?>" id="<?php echo $this->get_field_id('arm_no_record_msg'); ?>" class="widefat" value="<?php echo esc_attr($arm_no_record_msg); ?>">
			</p>
			<?php
		}

		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : "";
			$instance['arm_transaction_fields'] = !empty($new_instance['arm_transaction_fields']) ? $new_instance['arm_transaction_fields'] : '';
			$instance['arm_transaction_fields_label'] = !empty($new_instance['arm_transaction_fields_label']) ? $new_instance['arm_transaction_fields_label'] : '';
			$instance['arm_dis_invoice_btn'] = !empty($new_instance['arm_dis_invoice_btn']) ? $new_instance['arm_dis_invoice_btn'] : '';
			$instance['arm_invoice_btn_text'] = !empty($new_instance['arm_invoice_btn_text']) ? $new_instance['arm_invoice_btn_text'] : '';
			$instance['arm_invoice_css'] = !empty($new_instance['arm_invoice_css']) ? $new_instance['arm_invoice_css'] : '';
			$instance['arm_invoice_hover_css'] = !empty($new_instance['arm_invoice_hover_css']) ? $new_instance['arm_invoice_hover_css'] : '';
			$instance['arm_per_page'] = !empty($new_instance['arm_per_page']) ? $new_instance['arm_per_page'] : '';
			$instance['arm_no_record_msg'] = !empty($new_instance['arm_no_record_msg']) ? $new_instance['arm_no_record_msg'] : '';
			return $instance;
		}
	}

	if (class_exists('WP_Widget')) {
		function arm_register_widget_payment_transaction()
		{
			register_widget("ARM_Payment_Transaction_Widget");
		}
		add_action("widgets_init", "arm_register_widget_payment_transaction");
	}
}
/* end Payment Transaction widget*/

/* start User Current Membership widget*/
if (!class_exists('ARM_User_Current_Membership_Widget')) {
	class ARM_User_Current_Membership_Widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				"arm_member_widget_current_membership",
				__("ARMember Current Membership", "ARMember"),
				array(
					"description"  => __("Display current membership plan for logged in users if they purchased.", "ARMember")
				)
			);
		}

		public function widget($args, $instance)
		{
			if (!is_user_logged_in()) return;

			global $ARMember;
			$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : __("Current Membership", "ARMember");
			$arm_setup = isset($instance['arm_setup']) && !empty($instance['arm_setup']) ? $instance['arm_setup'] : '';

			$arm_dis_renew_btn = isset($instance['arm_dis_renew_btn']) && !empty($instance['arm_dis_renew_btn']) ? $instance['arm_dis_renew_btn'] : 'false';
			$arm_renew_btn_text = isset($instance['arm_renew_btn_text']) && !empty($instance['arm_renew_btn_text']) ? $instance['arm_renew_btn_text'] : __("Renew", "ARMember");
			$arm_payment_btn_text = isset($instance['arm_payment_btn_text']) && !empty($instance['arm_payment_btn_text']) ? $instance['arm_payment_btn_text'] : __("Make Payment", "ARMember");
			$arm_renew_css = isset($instance['arm_renew_css']) && !empty($instance['arm_renew_css']) ? $instance['arm_renew_css'] : '';
			$arm_renew_hove_css = isset($instance['arm_renew_hove_css']) && !empty($instance['arm_renew_hove_css']) ? $instance['arm_renew_hove_css'] : '';
			
			$arm_dis_cancel_btn = isset($instance['arm_dis_cancel_btn']) && !empty($instance['arm_dis_cancel_btn']) ? $instance['arm_dis_cancel_btn'] : 'false';
			$arm_cancel_btn_text = isset($instance['arm_cancel_btn_text']) && !empty($instance['arm_cancel_btn_text']) ? $instance['arm_cancel_btn_text'] : __("Cancel", "ARMember");
			$arm_cancel_css = isset($instance['arm_cancel_css']) && !empty($instance['arm_cancel_css']) ? $instance['arm_cancel_css'] : '';
			$arm_cancel_hover_css = isset($instance['arm_cancel_hover_css']) && !empty($instance['arm_cancel_hover_css']) ? $instance['arm_cancel_hover_css'] : '';
			$arm_sub_cancel_msg = isset($instance['arm_sub_cancel_msg']) && !empty($instance['arm_sub_cancel_msg']) ? $instance['arm_sub_cancel_msg'] : __("Your subscription has been cancelled.", "ARMember");
			
			$arm_dis_update_btn = isset($instance['arm_dis_update_btn']) && !empty($instance['arm_dis_update_btn']) ? $instance['arm_dis_update_btn'] : 'false';
			$arm_update_btn_text = isset($instance['arm_update_btn_text']) && !empty($instance['arm_update_btn_text']) ? $instance['arm_update_btn_text'] : __("Update Card", "ARMember");
			$arm_update_css = isset($instance['arm_update_css']) && !empty($instance['arm_update_css']) ? $instance['arm_update_css'] : '';
			$arm_update_hover_css = isset($instance['arm_update_hover_css']) && !empty($instance['arm_update_hover_css']) ? $instance['arm_update_hover_css'] : '';
			
			$arm_trial_active_label = isset($instance['arm_trial_active_label']) && !empty($instance['arm_trial_active_label']) ? $instance['arm_trial_active_label'] : __("trial active", "ARMember");
			$arm_no_record_msg = isset($instance['arm_no_record_msg']) && !empty($instance['arm_no_record_msg']) ? $instance['arm_no_record_msg'] : __("There is no membership found.", "ARMember");

			$arm_membership_fields = isset($instance['arm_membership_fields']) && !empty($instance['arm_membership_fields']) ? $instance['arm_membership_fields'] : array();
			$arm_membership_fields_label = isset($instance['arm_membership_fields_label']) && !empty($instance['arm_membership_fields_label']) ? $instance['arm_membership_fields_label'] : array();
			
			if (empty($arm_setup) || empty($arm_membership_fields) || empty($arm_membership_fields_label)) return;

			foreach ($arm_membership_fields_label as $key => $value) {
				if (!in_array($key, $arm_membership_fields)) {
					unset($arm_membership_fields_label[$key]);
				}
			}
			$ARMember->set_front_css(true);
			$ARMember->set_front_js(true);
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }

            //echo do_shortcode('[arm_membership title="'.$arm_title.'" setup_id="'.$arm_setup.'" display_renew_button="'.$arm_dis_renew_btn.'" renew_text="'.$arm_renew_btn_text.'" make_payment_text="'.$arm_payment_btn_text.'" renew_css="'.$arm_renew_css.'" renew_hover_css="'.$arm_renew_hove_css.'" display_cancel_button="'.$arm_dis_cancel_btn.'" cancel_text="'.$arm_cancel_btn_text.'" cancel_css="'.$arm_cancel_css.'" cancel_hover_css="'.$arm_cancel_hover_css.'" cancel_message="'.$arm_sub_cancel_msg.'" display_update_card_button="'.$arm_dis_update_btn.'" update_card_text="'.$arm_update_btn_text.'" update_card_css="'.$arm_update_css.'" update_card_hover_css="'.$arm_update_hover_css.'" trial_active="'.$arm_trial_active_label.'" message_no_record="'.$arm_no_record_msg.'" membership_label="'.implode(",", $arm_membership_fields).'" membership_value="'.implode(",", $arm_membership_fields_label).'"]');
            echo do_shortcode('[arm_membership title="" setup_id="'.$arm_setup.'" display_renew_button="'.$arm_dis_renew_btn.'" renew_text="'.$arm_renew_btn_text.'" make_payment_text="'.$arm_payment_btn_text.'" renew_css="'.$arm_renew_css.'" renew_hover_css="'.$arm_renew_hove_css.'" display_cancel_button="'.$arm_dis_cancel_btn.'" cancel_text="'.$arm_cancel_btn_text.'" cancel_css="'.$arm_cancel_css.'" cancel_hover_css="'.$arm_cancel_hover_css.'" cancel_message="'.$arm_sub_cancel_msg.'" display_update_card_button="'.$arm_dis_update_btn.'" update_card_text="'.$arm_update_btn_text.'" update_card_css="'.$arm_update_css.'" update_card_hover_css="'.$arm_update_hover_css.'" trial_active="'.$arm_trial_active_label.'" message_no_record="'.$arm_no_record_msg.'" membership_label="'.implode(",", $arm_membership_fields).'" membership_value="'.implode(",", $arm_membership_fields_label).'"]');
			
			echo $args['after_widget'];
		}

		public function form($instance)
		{
			global $wpdb, $ARMember;
			$setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMember->tbl_arm_membership_setup."` ");
			if (empty($setups)) {
				_e("No any Configure Plan + Signup form founds. Please add this from ARMember > Configure Plan + Signup Page", "ARMember");
			} else {
				$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : __("Current Membership", "ARMember");
				$arm_setup = isset($instance['arm_setup']) && !empty($instance['arm_setup']) ? $instance['arm_setup'] : '';
				
				$arm_dis_renew_btn = isset($instance['arm_dis_renew_btn']) && !empty($instance['arm_dis_renew_btn']) ? $instance['arm_dis_renew_btn'] : 'false';
				$arm_renew_btn_text = isset($instance['arm_renew_btn_text']) && !empty($instance['arm_renew_btn_text']) ? $instance['arm_renew_btn_text'] : __("Renew", "ARMember");
				$arm_payment_btn_text = isset($instance['arm_payment_btn_text']) && !empty($instance['arm_payment_btn_text']) ? $instance['arm_payment_btn_text'] : __("Make Payment", "ARMember");
				$arm_renew_css = isset($instance['arm_renew_css']) && !empty($instance['arm_renew_css']) ? $instance['arm_renew_css'] : '';
				$arm_renew_hove_css = isset($instance['arm_renew_hove_css']) && !empty($instance['arm_renew_hove_css']) ? $instance['arm_renew_hove_css'] : '';
				
				$arm_dis_cancel_btn = isset($instance['arm_dis_cancel_btn']) && !empty($instance['arm_dis_cancel_btn']) ? $instance['arm_dis_cancel_btn'] : 'false';
				$arm_cancel_btn_text = isset($instance['arm_cancel_btn_text']) && !empty($instance['arm_cancel_btn_text']) ? $instance['arm_cancel_btn_text'] : __("Cancel", "ARMember");
				$arm_cancel_css = isset($instance['arm_cancel_css']) && !empty($instance['arm_cancel_css']) ? $instance['arm_cancel_css'] : '';
				$arm_cancel_hover_css = isset($instance['arm_cancel_hover_css']) && !empty($instance['arm_cancel_hover_css']) ? $instance['arm_cancel_hover_css'] : '';
				$arm_sub_cancel_msg = isset($instance['arm_sub_cancel_msg']) && !empty($instance['arm_sub_cancel_msg']) ? $instance['arm_sub_cancel_msg'] : __("Your subscription has been cancelled.", "ARMember");
				
				$arm_dis_update_btn = isset($instance['arm_dis_update_btn']) && !empty($instance['arm_dis_update_btn']) ? $instance['arm_dis_update_btn'] : 'false';
				$arm_update_btn_text = isset($instance['arm_update_btn_text']) && !empty($instance['arm_update_btn_text']) ? $instance['arm_update_btn_text'] : __("Update Card", "ARMember");
				$arm_update_css = isset($instance['arm_update_css']) && !empty($instance['arm_update_css']) ? $instance['arm_update_css'] : '';
				$arm_update_hover_css = isset($instance['arm_update_hover_css']) && !empty($instance['arm_update_hover_css']) ? $instance['arm_update_hover_css'] : '';
				
				$arm_trial_active_label = isset($instance['arm_trial_active_label']) && !empty($instance['arm_trial_active_label']) ? $instance['arm_trial_active_label'] : __("trial active", "ARMember");
				$arm_no_record_msg = isset($instance['arm_no_record_msg']) && !empty($instance['arm_no_record_msg']) ? $instance['arm_no_record_msg'] : __("There is no membership found.", "ARMember");

				$arm_membership_fields = isset($instance['arm_membership_fields']) && !empty($instance['arm_membership_fields']) ? $instance['arm_membership_fields'] : array('current_membership_no','current_membership_is','current_membership_recurring_profile','current_membership_started_on','current_membership_expired_on','current_membership_next_billing_date','action_button');
				$arm_membership_fields_label = isset($instance['arm_membership_fields_label']) && !empty($instance['arm_membership_fields_label']) ? $instance['arm_membership_fields_label'] : array();

				$current_membership_no = isset($arm_membership_fields_label['current_membership_no']) && !empty($arm_membership_fields_label['current_membership_no']) ? $arm_membership_fields_label['current_membership_no'] : __("No.", "ARMember");
				$current_membership_is = isset($arm_membership_fields_label['current_membership_is']) && !empty($arm_membership_fields_label['current_membership_is']) ? $arm_membership_fields_label['current_membership_is'] : __("Membership Plan", "ARMember");
				$current_membership_recurring_profile = isset($arm_membership_fields_label['current_membership_recurring_profile']) && !empty($arm_membership_fields_label['current_membership_recurring_profile']) ? $arm_membership_fields_label['current_membership_recurring_profile'] : __("Plan Type", "ARMember");
				$current_membership_started_on = isset($arm_membership_fields_label['current_membership_started_on']) && !empty($arm_membership_fields_label['current_membership_started_on']) ? $arm_membership_fields_label['current_membership_started_on'] : __("Starts On", "ARMember");
				$current_membership_expired_on = isset($arm_membership_fields_label['current_membership_expired_on']) && !empty($arm_membership_fields_label['current_membership_expired_on']) ? $arm_membership_fields_label['current_membership_expired_on'] : __("Expires On", "ARMember");
				$current_membership_next_billing_date = isset($arm_membership_fields_label['current_membership_next_billing_date']) && !empty($arm_membership_fields_label['current_membership_next_billing_date']) ? $arm_membership_fields_label['current_membership_next_billing_date'] : __("Cycle Date", "ARMember");
				$action_button = isset($arm_membership_fields_label['action_button']) && !empty($arm_membership_fields_label['action_button']) ? $arm_membership_fields_label['action_button'] : __("Action", "ARMember");

				?>

				<script type="text/javascript">
					function arm_update_membership_field(object)
					{
						var $this = jQuery(object);
						var value = $this.val();
						var data_id = $this.data("id");

						if (value == '') {
							return;
						}
						if (value == "true") {
							jQuery("."+data_id+"_fields").removeClass("arm_hidden");
						} else {
							jQuery("."+data_id+"_fields").addClass("arm_hidden");
						}
					}
				</script>

				<p>
					<label for="<?php echo $this->get_field_id("arm_title"); ?>"><?php _e("Title", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name("arm_title"); ?>" id="<?php echo $this->get_field_id("arm_title"); ?>" value="<?php echo esc_attr($arm_title); ?>" class="widefat">&nbsp;
				</p>

				<p>
					<label for="<?php echo $this->get_field_id("arm_setup"); ?>"><?php _e("Select Setup", "ARMember"); ?></label>
					<select name="<?php echo $this->get_field_name("arm_setup"); ?>" id="<?php echo $this->get_field_id("arm_setup"); ?>" class="widefat">
						<?php foreach ($setups as $setup) { ?>
							<option value="<?php echo $setup->arm_setup_id; ?>" <?php echo $setup->arm_setup_id == $arm_setup ? "selected='selected'" : ""; ?> ><?php echo stripslashes($setup->arm_setup_name); ?></option>
						<?php } ?>
					</select>
				</p>

				<p>
					<span class="arm_span"><?php _e("Current Membership", "ARMember"); ?></span>
					<div class="arm_membership_fields">
						<input type="checkbox" name="<?php echo $this->get_field_name('arm_membership_fields'); ?>[]" value="current_membership_no" <?php echo in_array('current_membership_no', $arm_membership_fields) ? "checked='checked'" : ""; ?> >
						<input type="text" name="<?php echo $this->get_field_name('arm_membership_fields_label'); ?>[current_membership_no]" value="<?php echo esc_attr($current_membership_no); ?>">
					</div>
					<div class="arm_membership_fields">
						<input type="checkbox" name="<?php echo $this->get_field_name('arm_membership_fields'); ?>[]" value="current_membership_is" <?php echo in_array('current_membership_is', $arm_membership_fields) ? "checked='checked'" : ""; ?> >
						<input type="text" name="<?php echo $this->get_field_name('arm_membership_fields_label'); ?>[current_membership_is]" value="<?php echo esc_attr($current_membership_is); ?>">
					</div>
					<div class="arm_membership_fields">
						<input type="checkbox" name="<?php echo $this->get_field_name('arm_membership_fields'); ?>[]" value="current_membership_recurring_profile" <?php echo in_array('current_membership_recurring_profile', $arm_membership_fields) ? "checked='checked'" : ""; ?> >
						<input type="text" name="<?php echo $this->get_field_name('arm_membership_fields_label'); ?>[current_membership_recurring_profile]" value="<?php echo esc_attr($current_membership_recurring_profile); ?>">
					</div>
					<div class="arm_membership_fields">
						<input type="checkbox" name="<?php echo $this->get_field_name('arm_membership_fields'); ?>[]" value="current_membership_started_on" <?php echo in_array('current_membership_started_on', $arm_membership_fields) ? "checked='checked'" : ""; ?> >
						<input type="text" name="<?php echo $this->get_field_name('arm_membership_fields_label'); ?>[current_membership_started_on]" value="<?php echo esc_attr($current_membership_started_on); ?>">
					</div>
					<div class="arm_membership_fields">
						<input type="checkbox" name="<?php echo $this->get_field_name('arm_membership_fields'); ?>[]" value="current_membership_expired_on" <?php echo in_array('current_membership_expired_on', $arm_membership_fields) ? "checked='checked'" : ""; ?> >
						<input type="text" name="<?php echo $this->get_field_name('arm_membership_fields_label'); ?>[current_membership_expired_on]" value="<?php echo esc_attr($current_membership_expired_on); ?>">
					</div>
					<div class="arm_membership_fields">
						<input type="checkbox" name="<?php echo $this->get_field_name('arm_membership_fields'); ?>[]" value="current_membership_next_billing_date" <?php echo in_array('current_membership_next_billing_date', $arm_membership_fields) ? "checked='checked'" : ""; ?> >
						<input type="text" name="<?php echo $this->get_field_name('arm_membership_fields_label'); ?>[current_membership_next_billing_date]" value="<?php echo esc_attr($current_membership_next_billing_date); ?>">
					</div>
					<div class="arm_membership_fields">
						<input type="checkbox" name="<?php echo $this->get_field_name('arm_membership_fields'); ?>[]" value="action_button" <?php echo in_array('action_button', $arm_membership_fields) ? "checked='checked'" : ""; ?> >
						<input type="text" name="<?php echo $this->get_field_name('arm_membership_fields_label'); ?>[action_button]" value="<?php echo esc_attr($action_button); ?>">
					</div>
				</p>

				<p>
					<span class="arm_span"><?php _e("Display Renew Subscription Button?", "ARMember"); ?></span>
					<label>
						<input type="radio" name="<?php echo $this->get_field_name('arm_dis_renew_btn'); ?>" data-id="renew" value="false" onChange="arm_update_membership_field(this);" <?php echo $arm_dis_renew_btn == "false" ? "checked='checked'" : ""; ?> >No
					</label>
					<label>
						<input type="radio" name="<?php echo $this->get_field_name('arm_dis_renew_btn'); ?>" data-id="renew" value="true" onChange="arm_update_membership_field(this);" <?php echo $arm_dis_renew_btn == "true" ? "checked='checked'" : ""; ?> >Yes
					</label>
				</p>
				<p class="membership_fields renew_fields <?php echo $arm_dis_renew_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_renew_btn_text'); ?>"><?php _e("Renew Text", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_renew_btn_text'); ?>" id="<?php echo $this->get_field_id('arm_renew_btn_text'); ?>" value="<?php echo esc_attr($arm_renew_btn_text); ?>" class="widefat">
				</p>
				<p class="membership_fields renew_fields <?php echo $arm_dis_renew_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_payment_btn_text'); ?>"><?php _e("Make Payment Text", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_payment_btn_text'); ?>" id="<?php echo $this->get_field_id('arm_payment_btn_text'); ?>" value="<?php echo esc_attr($arm_payment_btn_text); ?>" class="widefat">
				</p>
				<p class="membership_fields renew_fields <?php echo $arm_dis_renew_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_renew_css'); ?>"><?php _e("Button CSS", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_renew_css'); ?>" id="<?php echo $this->get_field_id('arm_renew_css'); ?>" value="<?php echo esc_attr($arm_renew_css); ?>" class="widefat">
				</p>
				<p class="membership_fields renew_fields <?php echo $arm_dis_renew_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_renew_hove_css'); ?>"><?php _e("Button Hover CSS", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_renew_hove_css'); ?>" id="<?php echo $this->get_field_id('arm_renew_hove_css'); ?>" value="<?php echo esc_attr($arm_renew_hove_css); ?>" class="widefat">
				</p>

				<p>
					<span class="arm_span"><?php _e("Display Cancel Subscription Button?", "ARMember"); ?></span>
					<label>
						<input type="radio" name="<?php echo $this->get_field_name('arm_dis_cancel_btn'); ?>" data-id="cancel" value="false" onChange="arm_update_membership_field(this);" <?php echo $arm_dis_cancel_btn == "false" ? "checked='checked'" : ""; ?> >No
					</label>
					<label>
						<input type="radio" name="<?php echo $this->get_field_name('arm_dis_cancel_btn'); ?>" data-id="cancel" value="true" onChange="arm_update_membership_field(this);" <?php echo $arm_dis_cancel_btn == "true" ? "checked='checked'" : ""; ?> >Yes
					</label>
				</p>

				<p class="membership_fields cancel_fields <?php echo $arm_dis_cancel_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_cancel_btn_text'); ?>"><?php _e("Cancel", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_cancel_btn_text'); ?>" id="<?php echo $this->get_field_id('arm_cancel_btn_text'); ?>" value="<?php echo esc_attr($arm_cancel_btn_text); ?>" class="widefat">
				</p>
				<p class="membership_fields cancel_fields <?php echo $arm_dis_cancel_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_cancel_css'); ?>"><?php _e("Button CSS", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_cancel_css'); ?>" id="<?php echo $this->get_field_id('arm_cancel_css'); ?>" value="<?php echo esc_attr($arm_cancel_css); ?>" class="widefat">
				</p>
				<p class="membership_fields cancel_fields <?php echo $arm_dis_cancel_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_cancel_hover_css'); ?>"><?php _e("Button Hover CSS", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_cancel_hover_css'); ?>" id="<?php echo $this->get_field_id('arm_cancel_hover_css'); ?>" value="<?php echo esc_attr($arm_cancel_hover_css); ?>" class="widefat">
				</p>
				<p class="membership_fields cancel_fields <?php echo $arm_dis_cancel_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_sub_cancel_msg'); ?>"><?php _e("Subscription Cancelled Message", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_sub_cancel_msg'); ?>" id="<?php echo $this->get_field_id('arm_sub_cancel_msg'); ?>" value="<?php echo esc_attr($arm_sub_cancel_msg); ?>" class="widefat">
				</p>

				<p>
					<span class="arm_span"><?php _e("Display Update Card Subscription Button?", "ARMember"); ?></span>
					<label>
						<input type="radio" name="<?php echo $this->get_field_name('arm_dis_update_btn'); ?>" data-id="update" value="false" onChange="arm_update_membership_field(this);" <?php echo $arm_dis_update_btn == "false" ? "checked='checked'" : ""; ?> >No
					</label>
					<label>
						<input type="radio" name="<?php echo $this->get_field_name('arm_dis_update_btn'); ?>" data-id="update" value="true" onChange="arm_update_membership_field(this);" <?php echo $arm_dis_update_btn == "true" ? "checked='checked'" : ""; ?> >Yes
					</label>
				</p>

				<p class="membership_fields update_fields <?php echo $arm_dis_update_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_update_btn_text'); ?>"><?php _e("Update Card Text", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_update_btn_text'); ?>" id="<?php echo $this->get_field_id('arm_update_btn_text'); ?>" value="<?php echo esc_attr($arm_update_btn_text); ?>" class="widefat">
				</p>
				<p class="membership_fields update_fields <?php echo $arm_dis_update_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_update_css'); ?>"><?php _e("Button CSS", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_update_css'); ?>" id="<?php echo $this->get_field_id('arm_update_css'); ?>" value="<?php echo esc_attr($arm_update_css); ?>" class="widefat">
				</p>
				<p class="membership_fields update_fields <?php echo $arm_dis_update_btn == 'false' ? 'arm_hidden' : ''; ?>">
					<label for="<?php echo $this->get_field_name('arm_update_hover_css'); ?>"><?php _e("Button Hover CSS", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_update_hover_css'); ?>" id="<?php echo $this->get_field_id('arm_update_hover_css'); ?>" value="<?php echo esc_attr($arm_update_hover_css); ?>" class="widefat">
				</p>

				<p>
					<label for="<?php echo $this->get_field_name('arm_trial_active_label'); ?>"><?php _e("Trial Active Label", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_trial_active_label'); ?>" id="<?php echo $this->get_field_id('arm_trial_active_label'); ?>" value="<?php echo esc_attr($arm_trial_active_label); ?>" class="widefat">
				</p>

				<p>
					<label for="<?php echo $this->get_field_name('arm_no_record_msg'); ?>"><?php _e("No Records Message", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_no_record_msg'); ?>" id="<?php echo $this->get_field_id('arm_no_record_msg'); ?>" value="<?php echo esc_attr($arm_no_record_msg); ?>" class="widefat">
				</p>
				<?php
			}
		}

		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : "";
			$instance['arm_setup'] = !empty($new_instance['arm_setup']) ? $new_instance['arm_setup'] : "";
			$instance['arm_membership_fields'] = !empty($new_instance['arm_membership_fields']) ? $new_instance['arm_membership_fields'] : "";
			$instance['arm_membership_fields_label'] = !empty($new_instance['arm_membership_fields_label']) ? $new_instance['arm_membership_fields_label'] : "";
			$instance['arm_dis_renew_btn'] = !empty($new_instance['arm_dis_renew_btn']) ? $new_instance['arm_dis_renew_btn'] : "";
			$instance['arm_renew_btn_text'] = !empty($new_instance['arm_renew_btn_text']) ? $new_instance['arm_renew_btn_text'] : "";
			$instance['arm_payment_btn_text'] = !empty($new_instance['arm_payment_btn_text']) ? $new_instance['arm_payment_btn_text'] : "";
			$instance['arm_renew_css'] = !empty($new_instance['arm_renew_css']) ? $new_instance['arm_renew_css'] : "";
			$instance['arm_renew_hove_css'] = !empty($new_instance['arm_renew_hove_css']) ? $new_instance['arm_renew_hove_css'] : "";
			$instance['arm_dis_cancel_btn'] = !empty($new_instance['arm_dis_cancel_btn']) ? $new_instance['arm_dis_cancel_btn'] : "";
			$instance['arm_cancel_btn_text'] = !empty($new_instance['arm_cancel_btn_text']) ? $new_instance['arm_cancel_btn_text'] : "";
			$instance['arm_cancel_css'] = !empty($new_instance['arm_cancel_css']) ? $new_instance['arm_cancel_css'] : "";
			$instance['arm_cancel_hover_css'] = !empty($new_instance['arm_cancel_hover_css']) ? $new_instance['arm_cancel_hover_css'] : "";
			$instance['arm_sub_cancel_msg'] = !empty($new_instance['arm_sub_cancel_msg']) ? $new_instance['arm_sub_cancel_msg'] : "";
			$instance['arm_dis_update_btn'] = !empty($new_instance['arm_dis_update_btn']) ? $new_instance['arm_dis_update_btn'] : "";
			$instance['arm_update_btn_text'] = !empty($new_instance['arm_update_btn_text']) ? $new_instance['arm_update_btn_text'] : "";
			$instance['arm_update_css'] = !empty($new_instance['arm_update_css']) ? $new_instance['arm_update_css'] : "";
			$instance['arm_update_hover_css'] = !empty($new_instance['arm_update_hover_css']) ? $new_instance['arm_update_hover_css'] : "";
			$instance['arm_trial_active_label'] = !empty($new_instance['arm_trial_active_label']) ? $new_instance['arm_trial_active_label'] : "";
			$instance['arm_no_record_msg'] = !empty($new_instance['arm_no_record_msg']) ? $new_instance['arm_no_record_msg'] : "";
			return $instance;
		}
	}

	if (class_exists('WP_Widget')) {
		function arm_register_widget_current_membership()
		{
			register_widget("ARM_User_Current_Membership_Widget");
		}
		add_action("widgets_init", "arm_register_widget_current_membership");
	}
}
/* end User Current Membership widget*/

/* start Drip Content widget*/
if (!class_exists('ARM_Drip_Content_Widget')) {
	class ARM_Drip_Content_Widget extends WP_Widget {
		public function __construct()
		{
			parent::__construct(
				"arm_member_widget_drip_content",
				__("ARMember Drip Content", "ARMember"),
				array(
					"description"  => __("", "ARMember")
				)
			);
			add_action('admin_init', array($this, 'arm_widget_script'));
		}

		public function arm_widget_script()
		{
			global $pagenow;

		    if ( 'widgets.php' === $pagenow ) {
		        wp_enqueue_script('arm-widget-script', MEMBERSHIP_URL.'/js/arm_widgets_js.js', array( 'jquery' ), false, true );
		    }
		}


		public function widget($args, $instance)
		{
			if (!is_user_logged_in()) return;
			
			$arm_title   = $instance['arm_title'];
			$arm_drip_rule = isset($instance['arm_drip_rule']) && !empty($instance['arm_drip_rule']) ? $instance['arm_drip_rule'] : '';
			$arm_drip_rule = isset($instance['arm_drip_rule']) && !empty($instance['arm_drip_rule']) ? $instance['arm_drip_rule'] : '';
			$arm_drip_content_text = isset($instance['arm_drip_content']) && !empty($instance['arm_drip_content']) ? $instance['arm_drip_content'] : '';
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];

			if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }
			$arm_drip_content_text = apply_filters('the_content',$arm_drip_content_text);

			// Display drip content.
			echo do_shortcode('[arm_drip_content id="'.$arm_drip_rule.'"]'.$arm_drip_content_text.'[/arm_drip_content]');
			
			echo $args['after_widget'];

		}

		public function form($instance)
		{
			global $arm_drip_rules, $arm_subscription_plans;
			$customDripRules = $arm_drip_rules->arm_get_custom_drip_rules();
			if (empty($customDripRules)) { ?>
				<p><?php esc_html_e("There is no any custom content drip rule found. Please add Drip Content rules from ARMember > Drip Content", "ARMember");?> </p>
			<?php } else {
				$arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : "";
				$arm_drip_rule = isset($instance['arm_drip_rule']) && !empty($instance['arm_drip_rule']) ? $instance['arm_drip_rule'] : '';
				$arm_drip_content = isset($instance['arm_drip_content']) && !empty($instance['arm_drip_content']) ? $instance['arm_drip_content'] : '';

				wp_enqueue_script('wp-tinymce');
				wp_enqueue_script('editorremov');
				?>

				<script type="text/javascript">
					jQuery(function ($) {
					    var options={
					        selector: '<?php echo $this->get_field_id('arm_drip_content'); ?>',
					        //selector: '.arm_drip_content.wp-editor-area',
					        height: 200,
					        theme: 'modern',
					        toolbar1: 'bold,italic,bullist,numlist,link',
					    };
					    quicktags({id : '<?php echo $this->get_field_id('arm_drip_content'); ?>'})
					    tinyMCE.init(options);
					    $(document).find('input[id*=savewidget]').hover(function () {
					        tinyMCE.triggerSave();
					    });

					    $(document).on('widget-updated', function (event, $widget) {
					        tinyMCE.remove();
					        tinyMCE.init(options);
					    });
					});				
				</script>

				<p>
					<label for="<?php echo $this->get_field_id('arm_title'); ?>"><?php echo _e("Title", "ARMember"); ?></label>
					<input type="text" name="<?php echo $this->get_field_name('arm_title'); ?>" id="<?php echo $this->get_field_id('arm_title'); ?>" value="<?php echo esc_attr($arm_title); ?>" class="widefat">&nbsp;
				</p>

				<p>
					<span class="arm_span"><?php _e("Select Drip Rule", "ARMember"); ?></span>
				</p>
					<?php
					$i = 0;
					foreach ($customDripRules as $rule) {
						$rule_type = isset($rule['arm_rule_type']) ? $rule['arm_rule_type'] : '';
						$rule_type_text = '--';
						switch ($rule_type) {
							case 'instant':
								$rule_type_text = __('Immediately', 'ARMember');
								break;
							case 'days':
								$days = isset($rule['rule_options']['days']) ? $rule['rule_options']['days'] : 0;
								$rule_type_text = __('After', 'ARMember') . ' ' . $days . ' ' . __('day(s) of subscription', 'ARMember');
								break;
							case 'dates':
								$rule_type_text = __('On specific date', 'ARMember');
								$from_date = isset($rule['rule_options']['from_date']) ? $rule['rule_options']['from_date'] : '';
								$to_date = isset($rule['rule_options']['to_date']) ? $rule['rule_options']['to_date'] : '';
								if (!empty($from_date)) {
									$rule_type_text .= '<br/>';
									$rule_type_text .= __('From', 'ARMember') . ': ' . $from_date;
								}
								if (!empty($to_date)) {
									$rule_type_text .= ' '.__('To', 'ARMember') . ': ' . $to_date;
								}
								break;
							default:
								break;
						}

						$subs_plan_title = '';
						if (!empty($rule['arm_rule_plans'])) {
							$plans_id = @explode(',', $rule['arm_rule_plans']);
							$subs_plan_title = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($plans_id);
							$subs_plan_title = (!empty($subs_plan_title)) ? $subs_plan_title : '';
						}
						?>
						<p>
						<input type="radio" id="<?php echo $this->id;?>-drip_rule-<?php echo $rule['arm_rule_id'];?>" name="<?php echo $this->get_field_name('arm_drip_rule'); ?>" value="<?php echo $rule['arm_rule_id'];?>" class="" <?php echo ($i == 0) ? 'checked="checked"' : '';?> > <label for="<?php echo $this->id;?>-drip_rule-<?php echo $rule['arm_rule_id'];?>"><?php echo $rule_type_text." -- ".$subs_plan_title; ?></label>
						</p>
						<?php $i++;
					} ?>
				

				<p>
					<label for="<?php echo $this->get_field_id("arm_drip_content"); ?>"><?php _e("Enter content which will be dripped", "ARMember"); ?></label>
					<?php
					$armshortcodecontent_editor = array(
						'textarea_name' => $this->get_field_name('arm_drip_content'),
						'textarea_rows' => 16,
						'editor_height' => '200px',
						'editor_class'  => 'arm_drip_content',
						'tinymce' => array(
					        'toolbar1' => 'bold, italic, bullist, numlist, link',
					        'toolbar2' => '',
					        'toolbar3' => '',
					    ),
					    'quicktags' => array(
					    	'buttons'  => 'strong,em,link,ul,ol,li,code'
					    ),
					    'tinymce' => false,
					);
					wp_editor($arm_drip_content, $this->get_field_id('arm_drip_content'), $armshortcodecontent_editor);
					?>
				</p>
				<?php
			}
		}

		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : '';
			$instance['arm_drip_rule'] = !empty($new_instance['arm_drip_rule']) ? $new_instance['arm_drip_rule'] : '';
			$instance['arm_drip_content'] = !empty($new_instance['arm_drip_content']) ? $new_instance['arm_drip_content'] : '';
			return $instance;
		}
	}

	if (class_exists('WP_Widget')) {
		global $arm_drip_rules;
		function arm_register_widget_drip_content()
		{
			register_widget("ARM_Drip_Content_Widget");
		}

		if(!empty($arm_drip_rules->isDripFeature))
		{
			add_action("widgets_init", "arm_register_widget_drip_content");
		}
	}
}
/* end Drip Content widget*/

/* start User Private Content widget*/
if (!class_exists('ARM_User_Private_Content_Widget')) {
    class ARM_User_Private_Content_Widget extends WP_Widget {
        public function __construct()
        {
            parent::__construct(
                "arm_member_widget_private_content",
                __("ARMember User Private Content", "ARMember"),
                array(
                    "description"  => __("Display Private content only to selected users.", "ARMember")
                )
            );
        }

        public function widget($args, $instance)
        {
            if (!is_user_logged_in()) return;

            global $ARMember;
            $arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : __("Title", "ARMember");
            
            $ARMember->set_front_css(true);
			$ARMember->set_front_js(true);
            // before and after widget arguments are defined by themes
            echo $args['before_widget'];

            if (!empty($arm_title)) {
                echo $args['before_title'] . apply_filters('widget_title', $arm_title) . $args['after_title'];
            }

            echo do_shortcode('[arm_user_private_content]');
            
            echo $args['after_widget'];
        }

        public function form($instance)
        {
            $arm_title = isset($instance['arm_title']) && !empty($instance['arm_title']) ? $instance['arm_title'] : __("Title", "ARMember"); ?>
            <p>
                <label for="<?php echo $this->get_field_id("arm_title"); ?>"><?php _e("Title", "ARMember"); ?></label>
                <input type="text" name="<?php echo $this->get_field_name("arm_title"); ?>" id="<?php echo $this->get_field_id("arm_title"); ?>" value="<?php echo esc_attr($arm_title); ?>" class="widefat">&nbsp;
            </p>
            <?php
        }

        public function update($new_instance, $old_instance)
        {
            $instance = array();
            $instance['arm_title'] = !empty($new_instance['arm_title']) ? strip_tags($new_instance['arm_title']) : "";
            return $instance;
        }
    }

    if (class_exists('WP_Widget')) {
        function arm_register_widget_private_content()
        {
            register_widget("ARM_User_Private_Content_Widget");
        }
        add_action("widgets_init", "arm_register_widget_private_content");
    }
}
/* end User Private Content widget*/

?>