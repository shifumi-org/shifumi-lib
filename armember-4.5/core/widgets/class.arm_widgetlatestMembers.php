<?php
if(!class_exists('ARMwidgetlatestMembers'))
{
	class ARMwidgetlatestMembers extends WP_Widget
	{
		function __construct()
		{
			parent::__construct(
				'arm_member_form_widget_latest_members',
				__('ARMember Latest Members', 'ARMember'),
				array('description' => __('Display Recently Registerd  Members', 'ARMember'))
			);
			add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
		}
		public function widget($args, $instance)
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_members_activity,$is_globalcss_added,$arm_social_feature,$arm_members_directory;
			echo $args['before_widget'];
                        
                        $date_format = $arm_global_settings->arm_get_wp_date_format();
			if (!empty($instance['title'])) {
				echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
			}

			$total_rec = $instance['total_rec'];
			$display_type = (!empty($instance['display_type'])) ? $instance['display_type'] : 'list';
			$slider_effect = (!empty($instance['slider_effect'])) ? $instance['slider_effect'] : 'slide';
			$arg = array(
				'orderby' => 'user_registered',
				'order' => 'DESC',
				'number' => ($total_rec + 10),
				'fields' => 'all',
                                'meta_key'     => 'arm_primary_status',
                                'meta_value'   => '1',
			);
			$users_admin = get_users($arg);
			$users = array();
            if (!empty($users_admin)) {
				$i = 1;
				foreach ($users_admin as $member_detail){
					$userID = $member_detail->ID;
                    if ($i > $total_rec) {
						continue;
					}
                    if (user_can($userID, 'administrator')) {
						continue;
                    }
                    $users[] = $member_detail;
					$i++;
                }
            }
			$frontfontstyle = $arm_global_settings->arm_get_front_font_style();
			$output = '';
			if(!empty($frontfontstyle['google_font_url'])){
				$output .= '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />';
			}
			if( $display_type == 'slider' ){
				$random_wrapper_key = arm_generate_random_code(6);
				$profile_template = $arm_members_directory->arm_get_template_by_id(1);
			    $profile_template_opt = $profile_template['arm_options'];
			    $default_cover = $profile_template_opt['default_cover'];
			    $output .= "<div class='arm_widget_slider_wrapper_container arm_slider_widget_{$random_wrapper_key}' data-effect='{$slider_effect}'>";
			    $u = 1;
			    $zindex = 1;
				foreach( $users as $us ){
					$random_inner_key = arm_generate_random_code(6);
					$output .= "<div class='arm_slider_widget_wrapper arm_widget_wrapper_{$random_inner_key}'>";
						$output .= "<style type='text/css' style='display:none;'>";
						$output .= ".arm_slider_widget_{$random_wrapper_key} .arm_widget_wrapper_{$random_inner_key} a:before{";
						$output .= "z-index:{$zindex} !important;";
						$output .= "}";
						$output .= ".arm_slider_widget_{$random_wrapper_key} .arm_widget_wrapper_{$random_inner_key} a span{";
						$output .= "z-index:".($zindex+1)." !important;";
						$output .= "}";
						$output .= "</style>";
						$user_id = $us->ID;
						$profile_cover = get_user_meta($user_id,'profile_cover',true);
					    if( $profile_cover == '' || empty($profile_cover) ){
					    	$profile_cover = $default_cover;
					    }
					    $profile_avatar = get_avatar($user_id,95);
						$output .= "<div class='arm_slider_widget_header'>";

							$output .= "<div class='arm_slider_widget_user_cover'>";
								$output .= "<img src='{$profile_cover}' style='width:100%;height:100%;border-radius:0;-webkit-border-radius:0;-o-border-radius:0;-moz-borde-radius:0;' />";
							$output .= "</div>";

							$output .= "<div class='arm_slider_widget_avatar'>";
								$output .= $profile_avatar;
							$output .= "</div>";

						$output .= "</div>";

						$output .= "<div class='arm_slider_widget_content_wrapper'>";
							$profile_link = $arm_global_settings->arm_get_user_profile_url($user_id);
                                                        $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
                                                        $arm_member_since_label = (isset($common_messages['arm_profile_member_since']) && $common_messages['arm_profile_member_since'] != '' ) ? $common_messages['arm_profile_member_since'] : __('Member Since', 'ARMember');
							$output .= "<a href='{$profile_link}' class='arm_slider_widget_profile_link'><span>".get_user_meta($user_id,'first_name',true).' '.get_user_meta($user_id,'last_name',true)."</span></a>";

							$output .= "<div class='arm_slider_widget_user_info'>";

								$output .= "<div class='arm_slider_widget_user_info_row'>";

									$output .= "<div class='arm_slider_widget_user_info_row_left'>";
										$output .= $arm_member_since_label;
									$output .= "</div>";

									$output .= "<div class='arm_slider_widget_user_info_row_right'>";
                                                                        
										$output .= date_i18n($date_format,strtotime($us->user_registered));
									$output .= "</div>";

								$output .= "</div>";

							$output .= "</div>";

						$output .= "</div>";
					$output .= "</div>";
					$u++;
					$zindex++;
				}
				$output .= "</div>";
				$output .= "<script type='text/javascript'>";
				$output .= "jQuery(document).ready(function (){arm_slider_widget_init();});";
				$output .= "</script>";
			} else {
				$output .= '<style type="text/css">';
				$membersWrapperClass = ".arm_widget_members";
					$output .= "
						$membersWrapperClass .arm_member_info_right a{
							{$frontfontstyle['frontOptions']['link_font']['font']}
						}
						$membersWrapperClass .arm_time_block{
							{$frontfontstyle['frontOptions']['level_4_font']['font']}
						}
					";
				$output .= '</style>';
				$get_global_css = $ARMember->arm_set_global_css(false);
				$output .= $get_global_css;
				$output .= '<div class="arm_widget_container arm_widget_members">';
				$output .= '<div class="arm_member_listing_container">';
				$output .= '<div class="arm_member_listing_wrapper">';
				if(!empty($users)){
					foreach ($users as $us)
					{
						$output .= "<div class='arm_member_info_block'>";
						$output .= "<div class='arm_member_info_left'>";
						$output .= "<div class='arm_user_avatar'>";
						$output .= get_avatar($us->ID, 60);
						$output .= "</div>";
						$output .= "</div>";
						$output .= "<div class='arm_member_info_right'>";
						$user_name = $us->first_name . ' ' . $us->last_name;
						if (empty($us->first_name) && empty($us->last_name)) {
							$user_name = $us->user_login;
						}
						if($arm_social_feature->isSocialFeature){
							$output .= "<a href='" . $arm_global_settings->arm_get_user_profile_url($us->ID) . "'>" . $user_name . "</a>";
						}
						else{
							$output .= $user_name;
						}
						$output .= "<span class='arm_time_block'>";
						$output .= __('Joined', 'ARMember');
						$output .= " " . $arm_global_settings->arm_time_elapsed(strtotime($us->user_registered));
						$output .= "</span>";
						$output .= "</div>";
						$output .= "</div>";
					}
				}
				$output .= '</div>';
				$output .= '<div class="armclear"></div>';
				$output .= '</div>';
				$output .= '</div>';
			}
			echo $output;
			echo $args['after_widget'];
		}
		public function form($instance)
		{
			global $arm_member_forms,$arm_widget_effects;
			$title = !empty($instance['title']) ? $instance['title'] : __('Recent Registerd Members', 'ARMember');
			$total_rec = !empty($instance['total_rec']) ? $instance['total_rec'] : 5;
			$display_type = !empty($instance['display_type']) ? $instance['display_type'] : 'list';
			$slider_effect = !empty($instance['slider_effect']) ? $instance['slider_effect'] : 'slide';
			if( empty($arm_widget_effects) ){
				$arm_widget_effects = array(
					'slide' => __('Slide','ARMember'),
					'crossfade' => __('Fade','ARMember'),
					'directscroll' => __('Direct Scroll','ARMember'),
					'cover' => __('Cover','ARMember'),
					'uncover' => __('Uncover','ARMember')
				);
			}
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title', 'ARMember');?>: </label>
				<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo esc_attr($title);?>">
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('total_rec');?>"><?php _e('Total Records To Display', 'ARMember');?>: </label>
				<input class="widefat" id="<?php echo $this->get_field_id('total_rec');?>" name="<?php echo $this->get_field_name('total_rec');?>" type="text" value="<?php echo esc_attr($total_rec);?>">
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('display_type');?>"><?php _e('Display Type','ARMember'); ?>: </label>
				<select class="widefat" name="<?php echo $this->get_field_name('display_type'); ?>">
					<option <?php selected($display_type,'list'); ?> value='list'><?php _e('List','ARMember'); ?></option>
					<option <?php selected($display_type,'slider'); ?> value='slider'><?php _e('Slider','ARMember'); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('slider_effect'); ?>"><?php _e('Slide Effect','ARMember'); ?></label>
				<select class="widefat" name="<?php echo $this->get_field_name('slider_effect'); ?>">
					<?php
						foreach( $arm_widget_effects as $value => $effect ){
							?>
							<option <?php selected($slider_effect,$value); ?> value='<?php echo $value; ?>'><?php echo $effect; ?></option>
							<?php
						}
					?>
				</select>
			</p>
			<?php
		}
		public function update($new_instance, $old_instance)
		{
			$instance = array();
			$instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
			$instance['total_rec'] = !empty($new_instance['total_rec']) ? $new_instance['total_rec'] : 5;
			$instance['display_type'] = !empty($new_instance['display_type']) ? $new_instance['display_type'] : 'list';
			$instance['slider_effect'] = !empty($new_instance['slider_effect']) ? $new_instance['slider_effect'] : 'slide';
			return $instance;
		}
		function scripts()
		{
			if (is_active_widget(false, false, $this->id_base, true)) {
				wp_enqueue_style('arm_front_css', MEMBERSHIP_URL . '/css/arm_front.css', array(), MEMBERSHIP_VERSION);
				wp_enqueue_style('arm_form_style_css', MEMBERSHIP_URL . '/css/arm_form_style.css', array(), MEMBERSHIP_VERSION);
				wp_enqueue_script('arm_carousel_slider_js',MEMBERSHIP_URL.'/js/jquery.carouFredSel.js',array(), MEMBERSHIP_VERSION);
				if (!wp_script_is('arm_common_js', 'enqueued')) {
					wp_enqueue_script('arm_common_js',MEMBERSHIP_URL.'/js/arm_common.js',array(),MEMBERSHIP_VERSION);
				}
			}
		}
	}
	if (class_exists('WP_Widget'))
	{
		function arm_register_latestMembers_widgets()
		{
			register_widget('ARMwidgetlatestMembers');
		}
		add_action('widgets_init', 'arm_register_latestMembers_widgets');
	}
}