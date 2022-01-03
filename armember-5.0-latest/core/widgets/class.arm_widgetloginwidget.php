<?php

if( !class_exists('ARMLoginWidget') ){
	class ARMLoginWidget extends WP_Widget{

		function __construct(){
			parent::__construct(
				'arm_member_form_login_widget',
				__('ARMember Login Widget', 'ARMember'),
				array('description' => __('Display currently logged in Member profile', 'ARMember'))
			);
			add_action('wp_enqueue_scripts',array($this,'scripts'));
		}

		public function widget($args,$instance){
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_members_activity,$is_globalcss_added,$arm_social_feature,$arm_members_directory;
			if( !is_user_logged_in() ){
				return;
			}
			$user_id = get_current_user_id();
			if( $user_id == '' || empty($user_id) || current_user_can('administrator') ){
				return;
			}
			echo $args['before_widget'];
			if (!empty($instance['title'])) {
				echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
			}
			$label1 = isset($instance['custom_meta_1']) ? $instance['custom_meta_1'] : '';
			$value1 = isset($instance['custom_meta_value_1']) ? $instance['custom_meta_value_1'] : '';
			$value1 = $this->arm_login_widget_user_meta_value($value1,$user_id);

			$label2 = isset($instance['custom_meta_2']) ? $instance['custom_meta_2'] : '';
			$value2 = isset($instance['custom_meta_value_2']) ? $instance['custom_meta_value_2'] : '';
			$value2 = $this->arm_login_widget_user_meta_value($value2,$user_id);

			$label3 = isset($instance['custom_meta_3']) ? $instance['custom_meta_3'] : '';
			$value3 = isset($instance['custom_meta_value_3']) ? $instance['custom_meta_value_3'] : '';
			$value3 = $this->arm_login_widget_user_meta_value($value3,$user_id);

			$output = "";
			$profile_template = $arm_members_directory->arm_get_template_by_id(1);
		    $profile_template_opt = $profile_template['arm_options'];
		    $default_cover = $profile_template_opt['default_cover'];
		    $profile_cover = get_user_meta($user_id,'profile_cover',true);
		    if( $profile_cover == '' || empty($profile_cover) ){
		    	$profile_cover = $default_cover;
		    }
		    $profile_avatar = get_avatar($user_id,95);
                    $rtl_class = '';
                    if (is_rtl()) {
                        $rtl_class = 'arm_rtl_widget';
                    }
			$output .= "<div class='arm_login_widget_wrapper ".$rtl_class."'>";
				$output .= "<div class='arm_login_widget_header'>";
					$output .= "<div class='arm_login_widget_user_cover'>";
						$output .= "<img src='{$profile_cover}' style='width:100%;height:100%;border-radius:0;-webkit-border-radius:0;-o-border-radius:0;-moz-borde-radius:0;' />";
					$output .= "</div>";
					$output .= "<div class='arm_login_widget_avatar'>";
						$output .= $profile_avatar;
					$output .= "</div>";
				$output .= "</div>";
				$output .= "<div class='arm_login_widget_content_wrapper'>";
				$profile_link = $arm_global_settings->arm_get_user_profile_url($user_id);
				$output .= "<a href='{$profile_link}' class='arm_login_widget_profile_link'><span>".get_user_meta($user_id,'first_name',true).' '.get_user_meta($user_id,'last_name',true)."</span></a>";
				$output .= "<div class='arm_login_widget_user_info'>";
				if( $label1 != '' ){
					$output .= "<div class='arm_login_widget_user_info_row'>";
						$output .= "<div class='arm_login_widget_user_info_row_left'>";
						$output .= $label1;
						$output .= "</div>";
						$output .= "<div class='arm_login_widget_user_info_row_right'>";
						$output .= $value1;
						$output .= "</div>";
					$output .= "</div>";
				}

				if( $label2 != '' ){
					$output .= "<div class='arm_login_widget_user_info_row'>";
						$output .= "<div class='arm_login_widget_user_info_row_left'>";
						$output .= $label2;
						$output .= "</div>";
						$output .= "<div class='arm_login_widget_user_info_row_right'>";
						$output .= $value2;
						$output .= "</div>";
					$output .= "</div>";
				}

				if( $label3 != '' ){
					$output .= "<div class='arm_login_widget_user_info_row'>";
						$output .= "<div class='arm_login_widget_user_info_row_left'>";
						$output .= $label3;
						$output .= "</div>";
						$output .= "<div class='arm_login_widget_user_info_row_right'>";
						$output .= $value3;
						$output .= "</div>";
					$output .= "</div>";
				}
				$output .= "</div>";
				$output .= "</div>";
			$output .= "</div>";
			echo $output;
			echo $args['after_widget'];
		}

		public function form($instance){
			global $arm_member_forms;
			$title = !empty($instance['title']) ? $instance['title'] : '';
			$custom_meta_1 = !empty($instance['custom_meta_1']) ? $instance['custom_meta_1'] : __('Joined Date','ARMember');
			$custom_meta_2 = !empty($instance['custom_meta_2']) ? $instance['custom_meta_2'] : '';
			$custom_meta_3 = !empty($instance['custom_meta_3']) ? $instance['custom_meta_3'] : '';
			$user_query = new WP_User_Query(array('fields'=>'all_with_meta','number'=>1));
			?>
			<p style="margin-bottom:0;">
				<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title', 'ARMember');?>: </label>
				<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo esc_attr($title);?>">&nbsp;
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('custom_meta_1'); ?>" style="float:left;width:100%;"><?php _e('User Meta 1','ARMember'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('custom_meta_1'); ?>" name="<?php echo $this->get_field_name('custom_meta_1'); ?>" type="text" value="<?php echo esc_attr($custom_meta_1); ?>"  style="float:left;width:120px;position: relative;top:1px;margin-right:5px;" />
				<?php
					$custom_field_1 = !empty($instance['custom_meta_value_1']) ? $instance['custom_meta_value_1'] : 'joined_date';
					$custom_field_2 = !empty($instance['custom_meta_value_2']) ? $instance['custom_meta_value_2'] : '';
					$custom_field_3 = !empty($instance['custom_meta_value_3']) ? $instance['custom_meta_value_3'] : '';
				?>
				<select name='<?php echo $this->get_field_name('custom_meta_value_1'); ?>' style="width:140px;">
					<option value=""><?php _e("Select User Meta",'ARMember'); ?></option>
					<option <?php selected($custom_field_1,'user_name'); ?> value="user_name"><?php _e('User Name','ARMember'); ?></option>
					<option <?php selected($custom_field_1,'first_name'); ?> value="first_name"><?php _e('First Name','ARMember'); ?></option>
					<option <?php selected($custom_field_1,'last_name'); ?> value="last_name"><?php _e('Last Name','ARMember'); ?></option>
					<option <?php selected($custom_field_1,'display_name'); ?> value="display_name"><?php _e('Display Name','ARMember'); ?></option>
					<option  <?php selected($custom_field_1,'joined_date'); ?> value="joined_date"><?php _e('Joined Date','ARMember'); ?></option>
					<option <?php selected($custom_field_1,'email'); ?> value="email"><?php _e('Email Address','ARMember'); ?></option>
					<option <?php selected($custom_field_1,'gender'); ?> value="gender"><?php _e('Gender','ARMember'); ?></option>
					<option <?php selected($custom_field_1,'url'); ?> value="url"><?php _e('Website','ARMember'); ?></option>
					<option <?php selected($custom_field_1,'country'); ?> value="country"><?php _e('Country/Region','ARMember'); ?></option>
					<option <?php selected($custom_field_1,'description'); ?> value="description"><?php _e('Biography','ARMember'); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('custom_meta_2'); ?>" style="float:left;width:100%;"><?php _e('User Meta 2','ARMember'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('custom_meta_2'); ?>" name="<?php echo $this->get_field_name('custom_meta_2'); ?>" type="text" value="<?php echo esc_attr($custom_meta_2); ?>" style="float:left;width:120px;position: relative;top:1px;margin-right:5px;" />
				<select name='<?php echo $this->get_field_name('custom_meta_value_2'); ?>' style="width:140px;">
					<option value=""><?php _e("Select User Meta",'ARMember'); ?></option>
					<option <?php selected($custom_field_2,'user_name'); ?> value="user_name"><?php _e('User Name','ARMember'); ?></option>
					<option <?php selected($custom_field_2,'first_name'); ?> value="first_name"><?php _e('First Name','ARMember'); ?></option>
					<option <?php selected($custom_field_2,'last_name'); ?> value="last_name"><?php _e('Last Name','ARMember'); ?></option>
					<option <?php selected($custom_field_2,'display_name'); ?> value="display_name"><?php _e('Display Name','ARMember'); ?></option>
					<option  <?php selected($custom_field_2,'joined_date'); ?> value="joined_date"><?php _e('Joined Date','ARMember'); ?></option>
					<option <?php selected($custom_field_2,'email'); ?> value="email"><?php _e('Email Address','ARMember'); ?></option>
					<option <?php selected($custom_field_2,'gender'); ?> value="gender"><?php _e('Gender','ARMember'); ?></option>
					<option <?php selected($custom_field_2,'url'); ?> value="url"><?php _e('Website','ARMember'); ?></option>
					<option <?php selected($custom_field_2,'country'); ?> value="country"><?php _e('Country/Region','ARMember'); ?></option>
					<option <?php selected($custom_field_2,'description'); ?> value="description"><?php _e('Biography','ARMember'); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('custom_meta_3'); ?>" style="float:left;width:100%;"><?php _e('User Meta 3','ARMember'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('custom_meta_3'); ?>" name="<?php echo $this->get_field_name('custom_meta_3'); ?>" type="text" value="<?php echo esc_attr($custom_meta_3); ?>" style="float:left;width:120px;position: relative;top:1px;margin-right:5px;" />
				<select name='<?php echo $this->get_field_name('custom_meta_value_3'); ?>' style="width:140px;">
					<option value=""><?php _e("Select User Meta",'ARMember'); ?></option>
					<option <?php selected($custom_field_3,'user_name'); ?> value="user_name"><?php _e('User Name','ARMember'); ?></option>
					<option <?php selected($custom_field_3,'first_name'); ?> value="first_name"><?php _e('First Name','ARMember'); ?></option>
					<option <?php selected($custom_field_3,'last_name'); ?> value="last_name"><?php _e('Last Name','ARMember'); ?></option>
					<option <?php selected($custom_field_3,'display_name'); ?> value="display_name"><?php _e('Display Name','ARMember'); ?></option>
					<option  <?php selected($custom_field_3,'joined_date'); ?> value="joined_date"><?php _e('Joined Date','ARMember'); ?></option>
					<option <?php selected($custom_field_3,'email'); ?> value="email"><?php _e('Email Address','ARMember'); ?></option>
					<option <?php selected($custom_field_3,'gender'); ?> value="gender"><?php _e('Gender','ARMember'); ?></option>
					<option <?php selected($custom_field_3,'url'); ?> value="url"><?php _e('Website','ARMember'); ?></option>
					<option <?php selected($custom_field_3,'country'); ?> value="country"><?php _e('Country/Region','ARMember'); ?></option>
					<option <?php selected($custom_field_3,'description'); ?> value="description"><?php _e('Biography','ARMember'); ?></option>
				</select>
			</p>
			<?php
		}

		public function update($new_instance,$old_instance){
			$instance = array();
			$instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
			$instance['custom_meta_1'] = !empty($new_instance['custom_meta_1']) ? $new_instance['custom_meta_1'] : __('Joined Date','ARMember');
			$instance['custom_meta_value_1'] = !empty($new_instance['custom_meta_value_1']) ? $new_instance['custom_meta_value_1'] : 'joined_date';

			$instance['custom_meta_2'] = !empty($new_instance['custom_meta_2']) ? $new_instance['custom_meta_2'] : '';
			$instance['custom_meta_value_2'] = !empty($new_instance['custom_meta_value_2']) ? $new_instance['custom_meta_value_2'] : '';

			$instance['custom_meta_3'] = !empty($new_instance['custom_meta_3']) ? $new_instance['custom_meta_3'] : '';
			$instance['custom_meta_value_3'] = !empty($new_instance['custom_meta_value_3']) ? $new_instance['custom_meta_value_3'] : '';
			return $instance;
		}

		public function scripts(){
			if (is_active_widget(false, false, $this->id_base, true)) {
				wp_enqueue_style('arm_front_css', MEMBERSHIP_URL . '/css/arm_front.css', array(), MEMBERSHIP_VERSION);
			}
		}

		public function arm_login_widget_user_meta_value($value = '',$user_id = ''){
			global $arm_global_settings;
			if( empty($user_id) ){
				return '';
			}
			$user = new WP_User($user_id);
                        $date_format = $arm_global_settings->arm_get_wp_date_format();
			switch($value){
				case 'user_name':
					return $user->data->user_login;
					break;
				case 'first_name':
					return get_user_meta($user_id,'first_name',true);
					break;
				case 'last_name':
					return get_user_meta($user_id,'last_name',true);
					break;
				case 'display_name':
					return $user->data->display_name;
					break;
				case 'email':
					return $user->data->user_email;
					break;
				case 'gender':
					return get_user_meta($user_id,'gender',true);
					break;
				case 'joined_date':
					return date_i18n($date_format,strtotime($user->data->user_registered));
					break;
				case 'description':
					return get_user_meta($user_id,'description',true);
					break;
				case 'url':
					return $user->data->user_url;
					break;
				case 'country':
					return get_user_meta($user_id,'country',true);
					break;
				default:
					return date_i18n($date_format,strtotime($user->data->user_registered));
					break;
			}
		}
	}
	if (class_exists('WP_Widget'))
	{
		function arm_register_login_widgets()
		{
			register_widget('ARMLoginWidget');
		}
		add_action('widgets_init', 'arm_register_login_widgets');
	}
}