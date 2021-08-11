<?php
if (!class_exists('ARM_buddypress_feature'))
{
	class ARM_buddypress_feature
	{
		var $isBuddypressFeature;
		function __construct()
		{
			global $wpdb, $ARMember, $arm_slugs;
			$is_buddypress_feature = get_option('arm_is_buddypress_feature');
			$this->isBuddypressFeature = ($is_buddypress_feature == '1') ? true : false;
                        if($this->isBuddypressFeature){
                            
                            $serialized_bp_settings = get_option('arm_buddypress_options');
                            $this->map_with_buddypress_avatar = $this->map_with_buddypress_profile_cover = $this->show_armember_profile = 0;
                            if(!empty($serialized_bp_settings)){
                               $unserialized_bp_settings = maybe_unserialize($serialized_bp_settings);
                                    $this->map_with_buddypress_avatar = isset($unserialized_bp_settings['avatar_map']) ? $unserialized_bp_settings['avatar_map'] : '';
                                    $this->map_with_buddypress_profile_cover = isset($unserialized_bp_settings['profile_cover_map']) ? $unserialized_bp_settings['profile_cover_map'] : '';
                                    $this->show_armember_profile = isset($unserialized_bp_settings['show_armember_profile']) ? $unserialized_bp_settings['show_armember_profile'] : 0;
                            }
                            add_filter('arm_custom_rule_types', array(&$this, 'arm_add_buddypress_option'), 10, 1);
                            add_filter('arm_prepare_custom_rule_data', array(&$this, 'arm_add_buddypress_lists'), 10, 2);
                            add_filter('arm_before_update_custom_access_rules', array($this, 'arm_arm_before_update_custom_access_rules'), 10, 3);
                            add_filter('bp_get_group_create_button', array(&$this, 'arm_bp_get_group_create_button'), 10, 1);
                            add_filter('bp_get_add_friend_button', array(&$this, 'arm_bp_get_add_friend_button'), 10, 1);
                            add_filter('bp_get_send_message_button_args', array(&$this, 'arm_bp_get_send_message_button_args'), 10, 1);
                            
                            add_filter('arm_is_allow_access', array(&$this, 'arm_check_buddypress_pages_access'), 10, 2);
                            add_action('bp_deactivation', array(&$this, 'arm_bp_deactivation'));
                            add_action('bp_activation', array(&$this, 'arm_bp_activation'));
                            
                            
                            if($this->map_with_buddypress_avatar){
                                add_action('xprofile_avatar_uploaded', array(&$this, 'arm_xprofile_avatar_uploaded_func'), 10, 3);
                                add_action('bp_core_delete_existing_avatar', array(&$this, 'arm_bp_core_delete_existing_avatar'));
                                
                                add_action('arm_remove_bp_avatar', array(&$this, 'arm_remove_bp_avatar_func'), 10, 1);
                                add_action('arm_after_upload_bp_avatar', array(&$this, 'arm_upload_bp_avatar_func'), 10, 1);
                            }
                            
                            if($this->map_with_buddypress_profile_cover){
                                add_action('xprofile_cover_image_deleted', array(&$this, 'arm_bp_core_delete_existing_profile_cover'));
                                add_action('xprofile_cover_image_uploaded', array(&$this, 'arm_xprofile_cover_image_uploaded_func'), 10, 1);
                                
                                add_action('arm_remove_bp_profile_cover', array(&$this, 'arm_remove_bp_profile_cover_func'), 10, 1);
                                add_action('arm_after_upload_bp_profile_cover', array(&$this, 'arm_upload_bp_profile_cover_func'), 10, 1);
                            }
                            
                            add_action('xprofile_updated_profile', array(&$this, 'arm_xprofile_updated_profile'), 11, 5);
                            add_action('arm_buddypress_xprofile_field_save', array(&$this, 'arm_buddypress_xprofile_field_save_func'), 10, 3);
                            
                            add_action('wp_ajax_arm_update_buddypress_settings', array(&$this, 'arm_update_buddypress_settings_func'));
                            add_action('wp_ajax_arm_buddypress_sync', array(&$this, 'arm_buddypress_sync_func'));
                            add_action('wp_ajax_arm_buddypress_sync_progress', array(&$this, 'arm_buddypress_sync_progress'));                            
                            // change link in buddypress
                            add_filter('bp_get_member_permalink', array(&$this, 'arm_show_armember_profile_func'));
                            add_filter('bp_core_get_user_domain', array(&$this, 'arm_show_armember_profile_link_func'), 10, 4);
                        }
		}
                
                function arm_show_armember_profile_link_func($domain, $user_id, $user_nicename, $user_login){
                    global $members_template, $arm_global_settings, $arm_social_feature, $wpdb, $ARMember;
                    $bp_core_get_user_domain = $domain;
                    $user_id = $user_id;
                    if(!empty($user_id)){
                        $profile_page_id = $this->show_armember_profile;
                        if(!empty($profile_page_id)){
                            $arm_profile_page_id = isset($arm_global_settings->global_settings['member_profile_page_id']) ? $arm_global_settings->global_settings['member_profile_page_id'] : 0;
                            if ($profile_page_id == $arm_profile_page_id) {
                                if ($arm_social_feature->isSocialFeature) {
                                    $templateOptions = $wpdb->get_var($wpdb->prepare("SELECT `arm_options` FROM {$ARMember->tbl_arm_member_templates} WHERE arm_type = %s", 'profile'));
                                    $display_admin_user = 0;
                                    if (!empty($templateOptions)) {
                                        $templateOptions = maybe_unserialize($templateOptions);
                                        $display_admin_user = isset($templateOptions['show_admin_users']) ? $templateOptions['show_admin_users'] : '';
                                    }
                                    $bp_core_get_user_domain = $arm_global_settings->arm_get_user_profile_url($user_id, $display_admin_user);
                                } 
                                else {
                                    $bp_core_get_user_domain = get_permalink($profile_page_id);
                                }
                            }
                            else
                            {
                                $bp_core_get_user_domain = get_permalink($profile_page_id);
                            }
                        }
                    }
                    return $bp_core_get_user_domain;
                }
                
                function arm_show_armember_profile_func( $bp_core_get_user_domain ){
                    global $members_template, $arm_global_settings, $arm_social_feature, $wpdb, $ARMember;
                    $user_id = $members_template->member->id;
                    if(!empty($user_id)){
                        $profile_page_id = $this->show_armember_profile;
                        if(!empty($profile_page_id)){
                        $arm_profile_page_id = isset($arm_global_settings->global_settings['member_profile_page_id']) ? $arm_global_settings->global_settings['member_profile_page_id'] : 0;

                        if ($profile_page_id == $arm_profile_page_id) {
                            if ($arm_social_feature->isSocialFeature) {
                                $templateOptions = $wpdb->get_var($wpdb->prepare("SELECT `arm_options` FROM {$ARMember->tbl_arm_member_templates} WHERE arm_type = %s", 'profile'));
                                $display_admin_user = 0;
                                if (!empty($templateOptions)) {
                                    $templateOptions = maybe_unserialize($templateOptions);
                                    $display_admin_user = isset($templateOptions['show_admin_users']) ? $templateOptions['show_admin_users'] : '';
                                }
                                $bp_core_get_user_domain = $arm_global_settings->arm_get_user_profile_url($user_id, $display_admin_user);
                            } 
                            else {
                                $bp_core_get_user_domain = get_permalink($profile_page_id);
                            }
                        }
                        else{
                                $bp_core_get_user_domain = get_permalink($profile_page_id);
                            }
                        }
                    }
                    return $bp_core_get_user_domain; 
                }
                
                function arm_buddypress_sync_func(){
                   global $ARMember, $wpdb, $arm_social_feature, $arm_capabilities_global;
                   $ARMember->arm_session_start();
                   $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                   @set_time_limit(0);
                   $sync_type = isset($_POST['sync_type']) ? $_POST['sync_type'] : ''; 
                    $response = array('type' => 'error', 'msg' => __('Something went wrong.', 'ARMember'));
                    $_SESSION['arm_bp_sync_users'] = 0;
                    if(!empty($sync_type)){
                        $amTotalUsers = get_users();
                        //$_SESSION['arm_bp_sync_users_total'] = count($amTotalUsers);
                        if (!empty($amTotalUsers)) {
                            if($sync_type == 'pull'){
                                foreach ($amTotalUsers as $usr) {
                                    $user_id = $usr->ID;
                                        $profile_groups = BP_XProfile_Group::get( array( 'fetch_fields' => true	) );
                                        if ( !empty( $profile_groups ) ) {
                                            foreach ( $profile_groups as $profile_group ) {
                                                if ( !empty( $profile_group->fields ) ) {				
                                                    foreach ( $profile_group->fields as $field ) {
                                                        $posted_field_id = $field->id;
                                                        if(!empty($posted_field_id)){
                                                            $data = $wpdb->get_results("SELECT `arm_form_field_option`, `arm_form_field_slug`  FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_bp_field_id` =". $posted_field_id);
                                                            if(!empty($data)){
                                                                foreach($data as $d){
                                                                    $arm_form_field_option = maybe_unserialize($d->arm_form_field_option);
                                                                    $arm_form_field_slug = $d->arm_form_field_slug;
                                                                    $field_type = $arm_form_field_option['type']; 

                                                                    $field = new BP_XProfile_Field( $posted_field_id );
                                                                    
                                                                    $user_meta_val = $field->data->value;
                                                                    if (function_exists('bp_get_profile_field_data')) {
                                                                        $user_meta_val = bp_get_profile_field_data('field='.$field->name.'&user_id='.$user_id);
                                                                    }
                                                                    
                                                                    if(in_array( $arm_form_field_slug, array('user_login', 'user_pass', 'avatar'))){

                                                                    }else if(in_array($arm_form_field_slug, array('user_email', 'user_url', 'display_name'))){
                                                                        wp_update_user( array( 'ID' => $user_id, $arm_form_field_slug => $user_meta_val ) );
                                                                    }
                                                                    else{
                                                                        if($field_type == 'file'){
                                                                            $uploaded_file = $field->data->value; 
                                                                            $exploded_uploaded_file = explode('/', $uploaded_file);
                                                                            $uploaded_file_name = $exploded_uploaded_file[count($exploded_uploaded_file) - 1];
                                                                            $uploaded_file_dir_path = bp_core_avatar_upload_path() . $uploaded_file;
                                                                            $arm_upload_file_path = MEMBERSHIP_UPLOAD_DIR .'/'. $uploaded_file_name;
                                                                            $user_meta_val = MEMBERSHIP_UPLOAD_URL .'/'. $uploaded_file_name;;

                                                                            global $wp_filesystem;
                                                                            if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                                                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                                                                                if (false === ($creds = request_filesystem_credentials($uploaded_file_dir_path, '', false, false) )) {
                                                                                    return true;
                                                                                }
                                                                                if (!WP_Filesystem($creds)) {
                                                                                    request_filesystem_credentials($uploaded_file_dir_path, $method, true, false);
                                                                                    return true;
                                                                                }
                                                                            }

                                                                            @$img = $wp_filesystem->get_contents($uploaded_file_dir_path);
                                                                            @$write_file = $wp_filesystem->put_contents($arm_upload_file_path, $img, FS_CHMOD_FILE);
                                                                        }
                                                                        
                                                                            update_user_meta($user_id, $arm_form_field_slug, $user_meta_val);
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        
                                    if($this->map_with_buddypress_avatar){
                                       
                                        $this->arm_xprofile_avatar_uploaded_func($user_id, '', array('object' => 'user'));
                                    }
                                    
                                    if($this->map_with_buddypress_profile_cover){
                                        
                                        $this->arm_xprofile_cover_image_uploaded_func($user_id);
                                    }
                                    $_SESSION['arm_bp_sync_users'] ++;
                                    @session_write_close();
                                    $ARMember->arm_session_start(true);
                                }
                            }
                            else if($sync_type == 'push'){
                                
                                foreach ($amTotalUsers as $usr) {
                                    $user_id = $usr->ID;
                                    $arm_form_id = get_user_meta($user_id,'arm_form_id', true);
                                        //$data = $wpdb->get_results("SELECT `arm_form_field_option`, `arm_form_field_bp_field_id`, `arm_form_field_slug` FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_slug` != '' AND `arm_form_field_status` = '1'");
					$data = $wpdb->get_results("SELECT `arm_form_field_option`, `arm_form_field_bp_field_id`, `arm_form_field_slug` FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_slug` != '' AND `arm_form_field_bp_field_id` != '0' AND `arm_form_field_status` = '1'");
                                      
                                        if(!empty($data)){
                                            foreach($data as $d){
                                                $arm_form_field_option = maybe_unserialize($d->arm_form_field_option);
                                                $arm_from_field_bp_field_id = $d->arm_form_field_bp_field_id;
                                                $arm_form_field_slug = $d->arm_form_field_slug;
                                                $arm_bp_map_field_type = $arm_form_field_option['type']; 
                                                if(!empty($arm_from_field_bp_field_id)) {

                                                    if(in_array($arm_form_field_slug, array("user_login","user_email"))) {
                                                        $arm_val = get_userdata($user_id)->{$arm_form_field_slug};
                                                    }
                                                    else {
                                                        $arm_val = get_user_meta($user_id, $arm_form_field_slug, true); 
                                                    }

                                                    
                                                    if ($arm_bp_map_field_type == 'checkbox') {
                                                        $arm_val = maybe_unserialize($arm_val);
                                                        if(is_array($arm_val)){
                                                            foreach ($arm_val as $key => $val) {
                                                                if ($val == '') {
                                                                    unset($arm_val[$key]);
                                                                }
                                                            }
                                                        }
                                                        $arm_val = maybe_serialize($arm_val);
                                                    } 
                                                    else if ($arm_bp_map_field_type == 'date') {
                                                        if (!empty($arm_val)) {
                                                            $form = new ARM_Form('id', $arm_form_id);
                                                            $form_settings = $form->settings;
                                                            $formDateFormat = '';
                                                            if (!empty($form) && !empty($form_settings['date_format'])) {
                                                                $formDateFormat = $form_settings['date_format'];
                                                            }

                                                            if (preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $arm_val, $match)) {
                                                                try{
                                                                        $date = new DateTime($arm_val);
                                                                } catch(Exception $e){
                                                                        $date1_ = str_replace('/','-',$arm_val);
                                                                        $date = new DateTime($date1_);
                                                                }

                                                                $arm_val = $date->format('Y-m-d H:i:s');
                                                            }
                                                            else{
                                                                $arm_val = date("Y-m-d H:i:s", strtotime($arm_val));
                                                            }
                                                        }
                                                    }
                                                    else if($arm_bp_map_field_type == 'file'){
                                                        if (!empty($arm_val)) {
                                                            $exploded_file = explode('/', $arm_val);
                                                            $uploaded_file_name = $exploded_file[count($exploded_file)-1];
                                                            $uploaded_file_dir = MEMBERSHIP_UPLOAD_DIR.'/'.$uploaded_file_name;
                                                            global $wp_filesystem;
                                                            if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                                                                if (false === ($creds = request_filesystem_credentials($uploaded_file_dir, '', false, false) )) {
                                                                    return true;
                                                                }
                                                                if (!WP_Filesystem($creds)) {
                                                                    request_filesystem_credentials($uploaded_file_dir, $method, true, false);
                                                                    return true;
                                                                }
                                                            }

                                                            $bp_upload_dir = bp_core_avatar_upload_path().'/profiles/'.$user_id;
                                                            if(!file_exists($bp_upload_dir)){
                                                                @mkdir($bp_upload_dir);
                                                            }

                                                            @$img = $wp_filesystem->get_contents($uploaded_file_dir);
                                                            @$write_file = $wp_filesystem->put_contents($bp_upload_dir.'/'.$uploaded_file_name, $img, FS_CHMOD_FILE);
                                                            $arm_val = '/profiles/'.$user_id.'/'.$uploaded_file_name;
                                                        }
                                                    }
                                                    $oldData = $wpdb->get_row("SELECT `id` FROM `" . $wpdb->prefix . "bp_xprofile_data` WHERE `field_id`=" . $arm_from_field_bp_field_id . " and `user_id`=" . $user_id);
                                                    if (!empty($oldData)) {
                                                        $wpdb->query("UPDATE " . $wpdb->prefix . "bp_xprofile_data set `value`='" . $arm_val . "',`last_updated`='" . current_time('mysql') . "' where `field_id`=" . $arm_from_field_bp_field_id . " and `user_id`=" . $user_id);
                                                    } else {
                                                        $wpdb->query("INSERT into " . $wpdb->prefix . "bp_xprofile_data (`field_id`,`user_id`,`value`,`last_updated`) values (" . $arm_from_field_bp_field_id . "," . $user_id . ",'" . $arm_val . "','" . current_time('mysql') . "')");
                                                    }
                                                }
                                            }
                                        }
                                    if($this->map_with_buddypress_avatar){
                                        $user_avatar = get_user_meta($user_id, 'avatar', true);
                                        if(!empty($user_avatar)){
                                            $this->arm_upload_bp_avatar_func($user_id);
                                        }
                                        else{
                                            $this->arm_remove_bp_avatar_func($user_id);
                                        }
                                    }

                                    if($this->map_with_buddypress_profile_cover){
                                        $user_profile_cover = get_user_meta($user_id, 'profile_cover', true);
                                        if(!empty($user_profile_cover)){
                                      
                                            $this->arm_upload_bp_profile_cover_func($user_id);
                                        }else{
                                            $this->arm_remove_bp_profile_cover_func($user_id);
                                        }
                                    }
                                    $_SESSION['arm_bp_sync_users'] ++;
                                    @session_write_close();
                                    $ARMember->arm_session_start(true);
                                }
                            }
                        }
                        $response = array('type' => 'success', 'msg' => __('Synced with buddypress successfully.', 'ARMember'));
                    }
                    
                    echo json_encode($response);
                    die();
                }

                function arm_buddypress_sync_progress() {
                    global $ARMember, $arm_capabilities_global;
                    $ARMember->arm_session_start();
                    $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                    $arm_total_users = isset($_POST['total_users']) ? (int) $_POST['total_users'] : 0;
                    $arm_synced_users = isset($_SESSION['arm_bp_sync_users']) ? (int) $_SESSION['arm_bp_sync_users'] : 0;
                    $response = array();
                    $response['total_users'] = $arm_total_users;
                    $response['currently_synced'] = $arm_synced_users;
                    if ($response['total_users'] == 0) {
                        $response['error'] = true;
                        $response['continue'] = false;
                    } else {
                        if ($response['currently_synced'] > 0) {
                            if ($response['currently_synced'] == $response['total_users']) {
                                $percentage = 100;
                                $response['continue'] = false;
                                unset($_SESSION['arm_bp_sync_users']);
                                //unset($_SESSION['arm_bp_sync_users_total']);
                            } else {
                                $percentage = (100 * $response['currently_synced']) / $response['total_users'];
                                $percentage = round($percentage);
                                $response['continue'] = true;
                            }
                            $response['percentage'] = $percentage;
                        } else {
                            $response['percentage'] = 0;
                            $response['continue'] = true;
                        }
                        $response['error'] = false;
                    }
                    echo json_encode(stripslashes_deep($response));
                    die();
                }

                function arm_update_buddypress_settings_func(){
                    global $ARMember, $wpdb, $arm_capabilities_global;
                    $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
                    $form_data = $_POST;
                    $action = sanitize_text_field($_POST['action']);
                    $buddpress_field_ids = $_POST['arm_buddypress_field_id'];
                    $map_buddypres_avatar = isset($_POST['map_with_buddypress_avatar']) ? intval($_POST['map_with_buddypress_avatar']) : 0;
                    $map_buddypress_profile_cover = isset($_POST['map_with_buddypress_profile_cover']) ? intval($_POST['map_with_buddypress_profile_cover']) : 0;
                    $show_armember_profile= !empty($_POST['show_armember_profile']) ? intval($_POST['show_armember_profile']) : 0;
                    
                    $is_update = true;
                    if($is_update == true){
                    if($action == 'arm_update_buddypress_settings'){
                        if(!empty($buddpress_field_ids)){
                            foreach ($buddpress_field_ids as $field_id => $bp_field_id){
                                $arm_field_options = $wpdb->get_var("select `arm_form_field_option` from ".$ARMember->tbl_arm_form_field." Where `arm_form_field_id` =". $field_id); 
                                
                                $unserialized_options = maybe_unserialize($arm_field_options); 
                                $unserialized_options['mapfield'] = $bp_field_id;
                                
                                $serialized_field_options = maybe_serialize($unserialized_options); 
                                $wpdb->update( 
                                        $ARMember->tbl_arm_form_field, 
                                        array( 
                                            'arm_form_field_bp_field_id' => $bp_field_id,
                                            'arm_form_field_option' => $serialized_field_options,
                                        ), 
                                        array( 'arm_form_field_id' => $field_id ), 
                                        array( 
                                                '%d',
                                            '%s',// value1
                                        ), 
                                        array( '%d' ) 
                                );
                                
                            }
                        }
                        
                       
                            $buddypress_settings_array = array('avatar_map'=> $map_buddypres_avatar,
                                'profile_cover_map' => $map_buddypress_profile_cover,
                                'show_armember_profile' => $show_armember_profile
                                    );
                            
                            update_option('arm_buddypress_options', $buddypress_settings_array);
                       
                    }
                        $response = array('type' => 'success', 'msg' => __('Settings Saved Successfully.', 'ARMember'));
                    }
                    else{
                        $response = array('type' => 'error', 'msg' => __('Something went wrong.', 'ARMember'));
                    }
                    echo json_encode($response);
                    die();
                    
                }
                
                function arm_xprofile_cover_image_uploaded_func( $user_id ){
                    global $arm_members_activity;
                    if(!empty($user_id)){
                           
                            $user_old_avatar = get_user_meta($user_id, 'profile_cover', true);
                            if(!empty($user_old_avatar)){
                                $explode_user_avatar = explode('/', $user_old_avatar);
                                $user_avatar_name = $explode_user_avatar[count($explode_user_avatar)-1];
                                $user_avatar_url = MEMBERSHIP_UPLOAD_DIR . '/'.$user_avatar_name;
                                
                                if(file_exists($user_avatar_url)){
                                    unlink($user_avatar_url);    
                                }
                                
                            }

                            $user_avatar_url = bp_attachments_get_attachment( 'url', array( 'item_id' => $user_id ) );

                            if(!empty($user_avatar_url)){

                                $exploded_avatar = explode("/", $user_avatar_url);
                                $avatar_name = $exploded_avatar[count($exploded_avatar)-1];

                                $file = $arm_members_activity->arm_upload_file_function($user_avatar_url, MEMBERSHIP_UPLOAD_DIR."/".$avatar_name);
                                
                                if (TRUE === $file) {
                                    update_user_meta($user_id, 'profile_cover', MEMBERSHIP_UPLOAD_URL."/".$avatar_name);
                                }
                            }
                    }
                }
                
                function arm_upload_bp_profile_cover_func( $user_id ){
                    if(!empty($user_id)){
                        $get_user_avatar = get_user_meta($user_id, 'profile_cover', true);
                            if(!empty($get_user_avatar)){
                                global $wp_filesystem, $ARMember;
                                $exploded_avatar = explode('/', $get_user_avatar);
                                $avatar_image = $exploded_avatar[count($exploded_avatar)-1];
                                $avatar_img_ext = explode(".", $avatar_image);
                                $avatar_img_name = $avatar_img_ext[0];
                                $arm_avatar_path = MEMBERSHIP_UPLOAD_DIR."/".$avatar_image;

                                $bp_avatar_dir = bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id.'/cover-image';
                                
                                
                                $bp_avatar_path = bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id.'/cover-image/'.$avatar_img_name.'-bp-cover-image.'.$avatar_img_ext[1];
                               
                                if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                                    if (false === ($creds = request_filesystem_credentials($arm_avatar_path, '', false, false) )) {
                                        return true;
                                    }
                                    if (!WP_Filesystem($creds)) {
                                        request_filesystem_credentials($arm_avatar_path, $method, true, false);
                                        return true;
                                    }
                                }

                                $buddypress_cover_image_url = bp_attachments_get_attachment( 'url', array( 'item_id' => $user_id ) );
                                
                                
                                if($wp_filesystem->is_dir($bp_avatar_dir))
                                { 
                                    if (!empty($buddypress_cover_image_url)) {
                                        $explode_user_avatar = explode('/', $buddypress_cover_image_url);
                                        $user_avatar_name = $explode_user_avatar[count($explode_user_avatar)-1];
                                        $user_avatar_url = bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id.'/cover-image/'. $user_avatar_name;
                                        @unlink($user_avatar_url);
                                    }
                                }else{
                                    if(!file_exists(bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id)){
                                        @mkdir(bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id);
                                        @mkdir(bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id.'/cover-image');
                                    }
                                    else{
                                        @mkdir(bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id.'/cover-image');
                                    }
                                }
                                
                                @$img = $wp_filesystem->get_contents($arm_avatar_path);
                                @$write_file = $wp_filesystem->put_contents($bp_avatar_path, $img, FS_CHMOD_FILE);
                            }
                        
                    }
                }
                
                function arm_remove_bp_profile_cover_func( $user_id ){
                    if(!empty($user_id)){
                        global $wp_filesystem;
                           $bp_avatar_dir = bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id.'/cover-image';
                            if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                                if (false === ($creds = request_filesystem_credentials($bp_avatar_dir, '', false, false) )) {
                                    return true;
                                }
                                if (!WP_Filesystem($creds)) {
                                    request_filesystem_credentials($bp_avatar_dir, $method, true, false);
                                    return true;
                                }
                            }
                            
                            
                            $buddypress_avatar_url = bp_attachments_get_attachment( 'url', array( 'item_id' => $user_id ) );

                            if($wp_filesystem->is_dir($bp_avatar_dir))

                                if (!empty($buddypress_avatar_url)) {
                                    $explode_user_avatar = explode('/', $buddypress_avatar_url);
                                    $user_avatar_name = $explode_user_avatar[count($explode_user_avatar)-1];
                                    $user_avatar_url = bp_core_avatar_upload_path() . '/buddypress/members/'.$user_id.'/cover-image/'. $user_avatar_name;
                                    @unlink($user_avatar_url);
                                }
                    }
                }
              
                function arm_upload_bp_avatar_func( $user_id ){
                    if(!empty($user_id)){
                        $get_user_avatar = get_user_meta($user_id, 'avatar', true);
                            if(!empty($get_user_avatar)){
                                global $wp_filesystem;
                                $exploded_avatar = explode('/', $get_user_avatar);
                                $avatar_image = $exploded_avatar[count($exploded_avatar)-1];
                                $avatar_img_ext = explode(".", $avatar_image);
                                $avatar_img_name = $avatar_img_ext[0];
                                $arm_avatar_path = MEMBERSHIP_UPLOAD_DIR."/".$avatar_image;

                                $bp_avatar_dir_main = bp_core_avatar_upload_path() . '/avatars/';
                                @mkdir($bp_avatar_dir_main);
                                
                                $bp_avatar_dir = $bp_avatar_dir_main.$user_id;
                                $bp_avatar_path = bp_core_avatar_upload_path() . '/avatars/'.$user_id.'/'.$avatar_img_name.'-bpfull.'.$avatar_img_ext[1];
                                $bp_avatar_bpthumb_path = bp_core_avatar_upload_path() . '/avatars/'.$user_id.'/'.$avatar_img_name.'-bpthumb.'.$avatar_img_ext[1];
                                
                                if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                                    if (false === ($creds = request_filesystem_credentials($arm_avatar_path, '', false, false) )) {
                                        return true;
                                    }
                                    if (!WP_Filesystem($creds)) {
                                        request_filesystem_credentials($arm_avatar_path, $method, true, false);
                                        return true;
                                    }
                                }

                                $buddypress_avatar_url = html_entity_decode( bp_core_fetch_avatar( array(
                                    'object'  => 'user',
                                    'item_id' => $user_id,
                                    'html'    => false,
                                    'type'    => 'full',
                                ) ) );
                                
                                $buddypress_thumb_url = html_entity_decode( bp_core_fetch_avatar( array(
                                        'object'  => 'user',
                                        'item_id' => $user_id,
                                        'html'    => false,
                                        'type'    => 'thumb',
                                ) ) );
                                if($wp_filesystem->is_dir($bp_avatar_dir))
                                { 
                                    if (strpos($buddypress_avatar_url, 'gravatar.com') === false) {
                                        $explode_user_avatar = explode('/', $buddypress_avatar_url);
                                        $user_avatar_name = $explode_user_avatar[count($explode_user_avatar)-1];
                                        $user_avatar_url = bp_core_avatar_upload_path() . '/avatars/'.$user_id.'/'. $user_avatar_name;
                                        @unlink($user_avatar_url);
                                    }

                                    if (strpos($buddypress_thumb_url, 'gravatar.com') === false) {
                                        $explode_user_thumb = explode('/', $buddypress_thumb_url);
                                        $user_thumb_name = $explode_user_thumb[count($explode_user_thumb)-1];
                                        $user_thumb_url = bp_core_avatar_upload_path() . '/avatars/'.$user_id.'/'. $user_thumb_name;
                                        @unlink($user_thumb_url);
                                    }
                                    $remove = $wp_filesystem->rmdir($bp_avatar_dir);
                                }
                                @mkdir($bp_avatar_dir);
                                $thumb_width = bp_core_avatar_thumb_width();
                                $thumb_height = bp_core_avatar_thumb_height();
                                $full_width = bp_core_avatar_full_width();
                                $full_height = bp_core_avatar_full_height();
                                $this->arm_resize_image($arm_avatar_path, $bp_avatar_path, $full_width, $full_height);
                                $this->arm_resize_image($arm_avatar_path, $bp_avatar_bpthumb_path, $thumb_width, $thumb_height);
                            }
                    }
                }
                
                function arm_resize_image($image_url, $resize_url, $img_w, $img_h){
                    $info = getimagesize($image_url);
                    if ($info['mime'] == 'image/gif') {
                        $original_info = getimagesize($image_url);
                        $original_w = $original_info[0];
                        $original_h = $original_info[1];
                        $original_img = imagecreatefromgif($image_url);
                        $thumb_img = imagecreatetruecolor($img_w, $img_h);
                        imagecopyresized($thumb_img, $original_img, 0, 0, 0, 0, $img_w, $img_h, $original_w, $original_h);
                        imagegif($thumb_img, $resize_url);
                    } else if ($info['mime'] == 'image/png') {
                        $original_info = getimagesize($image_url);
                        $original_w = $original_info[0];
                        $original_h = $original_info[1];
                        $original_img = imagecreatefrompng($image_url);
                        $thumb_img = imagecreatetruecolor($img_w, $img_h);
                        imagealphablending($thumb_img, false);
                        imagesavealpha($thumb_img, true);
                        imagecopyresized($thumb_img, $original_img, 0, 0, 0, 0, $img_w, $img_h, $original_w, $original_h);
                        imagepng($thumb_img, $resize_url);
                    } else {                        
                        $original_info = getimagesize($image_url);
                        $original_w = $original_info[0];
                        $original_h = $original_info[1];
                        $original_img = imagecreatefromjpeg($image_url);
                        $thumb_img = imagecreatetruecolor($img_w, $img_h);
                        imagecopyresized($thumb_img, $original_img, 0, 0, 0, 0, $img_w, $img_h, $original_w, $original_h);
                        imagejpeg($thumb_img, $resize_url);
                    }
                    return $resize_url;
                }
                
                function arm_remove_bp_avatar_func( $user_id ){
              
                    if(!empty($user_id)){
                        global $wp_filesystem;
                           $bp_avatar_dir = bp_core_avatar_upload_path() . '/avatars/'.$user_id;
                            if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                                if (false === ($creds = request_filesystem_credentials($bp_avatar_dir, '', false, false) )) {
                                    return true;
                                }
                                if (!WP_Filesystem($creds)) {
                                    request_filesystem_credentials($bp_avatar_dir, $method, true, false);
                                    return true;
                                }
                            }
                            
                            
                            $buddypress_avatar_url = html_entity_decode( bp_core_fetch_avatar( array(
                                    'object'  => 'user',
                                    'item_id' => $user_id,
                                    'html'    => false,
                                    'type'    => 'full',
                            ) ) );

                            $buddypress_thumb_url = html_entity_decode( bp_core_fetch_avatar( array(
                                    'object'  => 'user',
                                    'item_id' => $user_id,
                                    'html'    => false,
                                    'type'    => 'thumb',
                            ) ) );

                            if($wp_filesystem->is_dir($bp_avatar_dir))

                                if (strpos($buddypress_avatar_url, 'gravatar.com') === false) {
                                    $explode_user_avatar = explode('/', $buddypress_avatar_url);
                                    $user_avatar_name = $explode_user_avatar[count($explode_user_avatar)-1];
                                    $user_avatar_url = bp_core_avatar_upload_path() . '/avatars/'.$user_id.'/'. $user_avatar_name;
                                    @unlink($user_avatar_url);
                                }

                                if (strpos($buddypress_thumb_url, 'gravatar.com') === false) {
                                    $explode_user_thumb = explode('/', $buddypress_thumb_url);
                                    $user_thumb_name = $explode_user_thumb[count($explode_user_thumb)-1];
                                    $user_thumb_url = bp_core_avatar_upload_path() . '/avatars/'.$user_id.'/'. $user_thumb_name;
                                    @unlink($user_thumb_url);
                                }

                                @$wp_filesystem->rmdir($bp_avatar_dir);
                    }
                }
                
                function arm_xprofile_updated_profile( $user_id, $posted_field_ids, $errors, $old_values, $new_values ){
              
                    if(!empty($user_id) && !empty($posted_field_ids)){
                        
                        global $ARMember, $wpdb;
                            foreach($posted_field_ids as $posted_field_id){
                                if(!empty($posted_field_id)){
                                    $data = $wpdb->get_results("SELECT `arm_form_field_option`, `arm_form_field_slug`  FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_bp_field_id` =". $posted_field_id);
                                    if(!empty($data)){
                                        foreach($data as $d){
                                            $arm_form_field_option = maybe_unserialize($d->arm_form_field_option);
                                            $arm_form_field_slug = $d->arm_form_field_slug;
                                            $field_type = $arm_form_field_option['type']; 
                                            $field = new BP_XProfile_Field( $posted_field_id );
                                            
                                            $user_meta_val = $field->data->value; 
                                            if (function_exists('bp_get_profile_field_data')) {
                                                $user_meta_val = bp_get_profile_field_data('field='.$field->name.'&user_id='.$user_id);
                                            }
                                            if(in_array( $arm_form_field_slug, array('user_login', 'user_pass', 'avatar'))){

                                            }else if(in_array($arm_form_field_slug, array('user_email', 'user_url', 'display_name'))){
                                                wp_update_user( array( 'ID' => $user_id, $arm_form_field_slug => $user_meta_val ) );
                                            }
                                            else{
                                                if($field_type == 'file'){
                                                    $uploaded_file = $field->data->value; 
                                                    $exploded_uploaded_file = explode('/', $uploaded_file);
                                                    $uploaded_file_name = $exploded_uploaded_file[count($exploded_uploaded_file) - 1];
                                                    $uploaded_file_dir_path = bp_core_avatar_upload_path() . $uploaded_file;
                                                    $arm_upload_file_path = MEMBERSHIP_UPLOAD_DIR .'/'. $uploaded_file_name;
                                                    $user_meta_val = MEMBERSHIP_UPLOAD_URL .'/'. $uploaded_file_name;;

                                                    global $wp_filesystem;
                                                    if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                                                        if (false === ($creds = request_filesystem_credentials($uploaded_file_dir_path, '', false, false) )) {
                                                            return true;
                                                        }
                                                        if (!WP_Filesystem($creds)) {
                                                            request_filesystem_credentials($uploaded_file_dir_path, $method, true, false);
                                                            return true;
                                                        }
                                                    }

                                                    @$img = $wp_filesystem->get_contents($uploaded_file_dir_path);
                                                    @$write_file = $wp_filesystem->put_contents($arm_upload_file_path, $img, FS_CHMOD_FILE);
                                                }
                                                if($field_type == 'checkbox'){
                                                    $user_meta_val = maybe_unserialize($user_meta_val);
                                                    update_user_meta($user_id, $arm_form_field_slug, $user_meta_val);    
                                                } else {
                                                    update_user_meta($user_id, $arm_form_field_slug, $user_meta_val);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                          
                    }
                }
                
                function arm_buddypress_xprofile_field_save_func($user_id, $posted_data = array(), $action='')
		{
                    global $wpdb, $ARMember;

                    $arm_new_array = array();
                    $arm_new_array = $posted_data;

                    $unser_array = array(
                        'id', 'form', 'repeat_email', 'repeat_pass', '_country', 'referral_url', 'arm_form_id', 'arm_nonce_check',
                        'arm_plan_type', 'arm_primary_status', 'arm_secondary_status', 'arm_user_plan',
                        'isAdmin', 'action', 'redirect_to', 'arm_action', 'page_id', 'form_filter_kp', 'form_filter_st', 'nonce_check', 
                    );
                    if ($action == 'add') {
                            $arm_form_id = $posted_data['arm_form_id'];
                    } else {
                            $unser_array[] = 'roles';
                            $arm_form_id = get_user_meta($user_id, 'arm_form_id', true);
                    }

                    foreach ($unser_array as $key) {
                            if (isset($arm_new_array[$key])) {
                                    unset($arm_new_array[$key]);
                            }
                    }
                    if (!empty($user_id) && !empty($arm_new_array)) {
                        foreach ($arm_new_array as $arm_key => $arm_val) {
                                $data = $wpdb->get_results("SELECT `arm_form_field_option`, `arm_form_field_bp_field_id` FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_slug` ='" . $arm_key . "'");
                         
                                if (!empty($data)) {
                                    foreach($data as $d){
                                    
                                    $arm_form_field_option = maybe_unserialize($d->arm_form_field_option);
                                    $arm_bp_field_map_id = $d->arm_form_field_bp_field_id;
                                    if (!empty($arm_bp_field_map_id)) {
                                            $arm_bp_map_field_id = $arm_bp_field_map_id;
                                            $arm_bp_map_field_type = $arm_form_field_option['type'];

                                            if ($arm_bp_map_field_type == 'checkbox') {
                                                $arm_val = maybe_unserialize($arm_val);
                                                if(is_array($arm_val)){
                                                    foreach ($arm_val as $key => $val) {
                                                        if ($val == '') {
                                                            unset($arm_val[$key]);
                                                        }
                                                    }
                                                  
                                                    $arm_val = maybe_serialize(array_values($arm_val));
                                                }
                                            } 
                                            else if ($arm_bp_map_field_type == 'date') {
                                                if (!empty($arm_val)) {
                                                    $form = new ARM_Form('id', $arm_form_id);
                                                    $form_settings = $form->settings;
                                                    $formDateFormat = '';
                                                    if (!empty($form) && !empty($form_settings['date_format'])) {
                                                        $formDateFormat = $form_settings['date_format'];
                                                    }
                                                    
                                                    if (preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $arm_val, $match)) {
                                                        try{
                                                                $date = new DateTime($arm_val);
                                                        } catch(Exception $e){
                                                                $date1_ = str_replace('/','-',$arm_val);
                                                                $date = new DateTime($date1_);
                                                        }

                                                        $arm_val = $date->format('Y-m-d H:i:s');
                                                    }
                                                    else{
                                                        $arm_val = date("Y-m-d H:i:s", strtotime($arm_val));
                                                    }
                                                }
                                            }
                                            else if($arm_bp_map_field_type == 'file'){
                                                if (!empty($arm_val)) {
                                                    $exploded_file = explode('/', $arm_val);
                                                    $uploaded_file_name = $exploded_file[count($exploded_file)-1];
                                                    $uploaded_file_dir = MEMBERSHIP_UPLOAD_DIR.'/'.$uploaded_file_name;
                                                    global $wp_filesystem;
                                                    if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
                                                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                                                        if (false === ($creds = request_filesystem_credentials($uploaded_file_dir, '', false, false) )) {
                                                            return true;
                                                        }
                                                        if (!WP_Filesystem($creds)) {
                                                            request_filesystem_credentials($uploaded_file_dir, $method, true, false);
                                                            return true;
                                                        }
                                                    }
                                                    
                                                    $bp_upload_dir = bp_core_avatar_upload_path().'/profiles/'.$user_id;
                                                    if(!file_exists($bp_upload_dir)){
                                                        @mkdir($bp_upload_dir);
                                                    }
                                              
                                                    @$img = $wp_filesystem->get_contents($uploaded_file_dir);
                                                    @$write_file = $wp_filesystem->put_contents($bp_upload_dir.'/'.$uploaded_file_name, $img, FS_CHMOD_FILE);
                                                    $arm_val = '/profiles/'.$user_id.'/'.$uploaded_file_name;
                                                }
                                            }
                                            $oldData = $wpdb->get_row("SELECT `id` FROM `" . $wpdb->prefix . "bp_xprofile_data` WHERE `field_id`=" . $arm_bp_map_field_id . " and `user_id`=" . $user_id);
                                            if (!empty($oldData)) {
                                                $wpdb->query("UPDATE " . $wpdb->prefix . "bp_xprofile_data set `value`='" . $arm_val . "',`last_updated`='" . current_time('mysql') . "' where `field_id`=" . $arm_bp_map_field_id . " and `user_id`=" . $user_id);
                                            } else {
                                                $wpdb->query("INSERT into " . $wpdb->prefix . "bp_xprofile_data (`field_id`,`user_id`,`value`,`last_updated`) values (" . $arm_bp_map_field_id . "," . $user_id . ",'" . $arm_val . "','" . current_time('mysql') . "')");
                                            }
                                        }
                                    }
                                }
                        }
                        
                        if($this->map_with_buddypress_avatar){
                            if(isset($posted_data['avatar']) && !empty($posted_data['avatar'])){
                                $this->arm_upload_bp_avatar_func($user_id);
                            }
                            else if(isset($posted_data['avatar'])){
                                $this->arm_remove_bp_avatar_func($user_id);
                            }
                        }
                            
                        if($this->map_with_buddypress_profile_cover){
                            if(isset($posted_data['profile_cover']) && !empty($posted_data['profile_cover'])){
                                $this->arm_upload_bp_profile_cover_func($user_id);
                            }else if(isset($posted_data['profile_cover'])){
                                $this->arm_remove_bp_profile_cover_func($user_id);
                            }
                        }
                    }
		}
                
                function arm_bp_core_delete_existing_avatar($args){
                    if(!empty($args)){
                        $item_type = $args['object'];
                        if($item_type == 'user'){
                            $item_id = $args['item_id'];
                            $user_avatar = get_user_meta($item_id, 'avatar', true);
                            if(!empty($user_avatar)){
                               
                                $explode_user_avatar = explode('/', $user_avatar);
                                $user_avatar_name = $explode_user_avatar[count($explode_user_avatar)-1];
                                $user_avatar_url = MEMBERSHIP_UPLOAD_DIR. '/' . $user_avatar_name;
                                @unlink($user_avatar_url);
                                delete_user_meta($item_id, 'avatar');
                            }
                        }
                    }
                }
                
                function arm_bp_core_delete_existing_profile_cover( $user_id ){
                    if(!empty($user_id)){
                            $user_avatar = get_user_meta($user_id, 'profile_cover', true);
                            if(!empty($user_avatar)){
                               
                                $explode_user_avatar = explode('/', $user_avatar);
                                $user_avatar_name = $explode_user_avatar[count($explode_user_avatar)-1];
                                $user_avatar_url = MEMBERSHIP_UPLOAD_DIR. '/' . $user_avatar_name;
                                @unlink($user_avatar_url);
                                delete_user_meta($user_id, 'profile_cover');
                            }
                    }
                }
                function arm_xprofile_avatar_uploaded_func($item_id, $item_type, $avatar_data){
                    global $ARMember, $arm_members_activity;

                    
                    if(!empty($item_id)){
                        if($avatar_data['object'] == 'user'){
                            $user_old_avatar = get_user_meta($item_id, 'avatar', true);
                            if(!empty($user_old_avatar)){
                                $explode_user_avatar = explode('/', $user_old_avatar);
                                $user_avatar_name = $explode_user_avatar[count($explode_user_avatar)-1];
                                $user_avatar_url = MEMBERSHIP_UPLOAD_DIR . '/'.$user_avatar_name;
                                @unlink($user_avatar_url);
                            }

                            $user_avatar_url = html_entity_decode( bp_core_fetch_avatar( array(
                                        'object'  => 'user',
                                        'item_id' => $item_id,
                                        'html'    => false,
                                        'type'    => 'full',
                                ) ) );

                            if(strpos($user_avatar_url, 'www.gravatar.com', 0) === false){

                                $exploded_avatar = explode("/", $user_avatar_url);
                                $avatar_name = $exploded_avatar[count($exploded_avatar)-1];

                                $file = $arm_members_activity->arm_upload_file_function($user_avatar_url, MEMBERSHIP_UPLOAD_DIR."/".$avatar_name);
                                
                                if (TRUE === $file) {

                                    update_user_meta($item_id, 'avatar', MEMBERSHIP_UPLOAD_URL."/".$avatar_name);
                                }
                            }
                        }
                    }
                }
                function arm_map_buddypress_armember_field_types(){
                    if(is_plugin_active('buddypress-xprofile-custom-fields-type/bp-xprofile-custom-fields-type.php')){
                        $maparray = array(
                            'color' => array('textbox', 'selectbox', 'radio', 'checkbox_acceptance','decimal_number', 'email', 'number_minmax', 'slider', 'web', 'number', 'select_custom_taxonomy', 'select_custom_post_type', 'url'),
                            'text' => array('textbox', 'selectbox', 'radio', 'checkbox_acceptance','decimal_number', 'email', 'number_minmax', 'slider', 'web', 'number',  'select_custom_taxonomy', 'select_custom_post_type', 'url'),
                            'email' => array('textbox', 'selectbox', 'radio', 'email'),
                            'url' => array('textbox', 'web', 'url'),
                            'password' => array('textbox'),
                            'date' => array('textbox', 'birthdate', 'datepicker', 'datebox'),
                            'file' => array('file', 'image'),
                            'textarea' => array('textbox', 'textarea'),
                            'select' => array('textbox', 'selectbox', 'radio', 'checkbox_acceptance', 'decimal_number', 'email', 'number_minmax', 'slider', 'number', 'select_custom_taxonomy', 'select_custom_post_type'),
                            'radio' => array('textbox', 'selectbox', 'radio', 'checkbox_acceptance', 'select_custom_taxonomy', 'select_custom_post_type'),
                            'checkbox' => array('checkbox','multiselectbox', 'multiselect_custom_taxonomy','multiselect_custom_post_type'),
                            'roles' => array('textbox', 'selectbox',  'radio'),
                        );
                    }
                    else
                    {
                        $maparray = array(
                            'color' => array('textbox', 'selectbox', 'radio', 'number', 'url'),
                            'text' => array('textbox', 'selectbox', 'radio', 'number', 'url'),
                            'email' => array('textbox', 'selectbox', 'radio'),
                            'url' => array('textbox', 'url'),
                            'password' => array('textbox'),
                            'date' => array('textbox', 'datebox'),
                            'file' => array(),
                            'textarea' => array('textbox', 'textarea'),
                            'select' => array('textbox', 'selectbox', 'radio', 'number'),
                            'radio' => array('textbox', 'selectbox', 'radio'),
                            'checkbox' => array('checkbox','multiselectbox'),
                            'roles' => array('textbox', 'selectbox',  'radio'),
                        );
                    }
                    return apply_filters('arm_map_buddypress_armember_fields', $maparray);
                }
                
		function arm_add_buddypress_option($rule_types = array())
		{
            global $is_bp_active;
            
			if ($is_bp_active != 1 && is_plugin_active('buddyboss-platform/bp-loader.php') && is_plugin_active('buddypress/bp-loader.php')) {
				if ($this->isBuddypressFeature) {
                    $rule_types['buddyboss'] = __('BuddyBoss', 'ARMember');
                }
			} elseif (is_plugin_active('buddypress/bp-loader.php')) {                
                if ($this->isBuddypressFeature) {
                    $rule_types['buddypress'] = __('BuddyPress', 'ARMember');
                }
            }
            
			return $rule_types;
		}
		function arm_add_buddypress_lists($rule_records = array(), $args = array())
		{
			$arm_contents = $this->get_contents();
			extract($args);
			if ($slug == 'buddypress' || $slug == 'buddyboss') {
				$planArr = array();
				if (!empty($plan) && $plan != 'all') {
					$planArr = explode(',', $plan);
				}
				foreach ($arm_contents as $arm_key => $arm_val) {
					$protect = $arm_val['protection'];
					$protect = (!empty($protect)) ? $protect : 0;
					$item_plans = (!empty($arm_val['plans'])) ? $arm_val['plans'] : array();
					$display = true;
					if ($protection != 'all' && $protection != $protect) {
						$display = false;
					}
					$planDiff = array_intersect($planArr, $item_plans);
					if (!empty($planArr) && empty($planDiff)) {
						$display = false;
						if ($protection == '0') {
							$display = true;
						}
					}
					if ($display) {
						$rule_records[$arm_key] = $arm_val;
					}
				}
			}
			return $rule_records;
		}
		function arm_arm_before_update_custom_access_rules($custom_rules = array(), $type_slug='', $arm_rules = object)
		{
			if ($type_slug == 'buddypress' || $type_slug == 'buddyboss') {
				foreach ($arm_rules as $item_id => $item_rule) {
					$item_rule = (array) $item_rule;
					if (empty($item_rule['protection']) || $item_rule['protection'] == '0') {
						unset($item_rule['plans']);
					} else {
						$item_rule['plans'] = (array) $item_rule['plans'];
						$item_rule['plans'] = array_keys($item_rule['plans']);
					}
					$custom_rules['buddypress'][$item_id] = $item_rule;
				}
			}
			return $custom_rules;
		}
		function get_contents()
		{
			global $arm_access_rules;
			$contents = array();
			$contents['buddypress_add_group'] = array(
				'id' => 'buddypress_add_group',
				'title' => __('Group creation', 'ARMember'),
				'description' => __('Only members can create new groups.', 'ARMember'),
			);
			$contents['buddypress_friendship'] = array(
				'id' => 'buddypress_friendship',
				'title' => __('Friendship request', 'ARMember'),
				'description' => __('Only allow members to send friendship requests.', 'ARMember'),
			);
			$contents['buddypress_priv_msg'] = array(
				'id' => 'buddypress_priv_msg',
				'title' => __('Private messaging', 'ARMember'),
				'description' => __('Only allow members to send private messages.', 'ARMember'),
			);
			$contents['buddypress_members'] = array(
				'id' => 'buddypress_members',
				'title' => __('Member listing', 'ARMember'),
				'description' => __('Only members can see the BuddyPress Member Directory and Member Profiles.', 'ARMember'),
			);
			$contents = apply_filters('arm_buddypress_content_list', $contents);
			$sp_setings = $arm_access_rules->arm_get_custom_access_rules('buddypress');
            foreach ($contents as $key => $page) {
                $sp_opts = isset($sp_setings[$key]) ? $sp_setings[$key] : array();
                $contents[$key]['protection'] = (!empty($sp_opts['protection'])) ? $sp_opts['protection'] : '0';
                $contents[$key]['plans'] = (!empty($sp_opts['plans'])) ? $sp_opts['plans'] : array();
            }
			return $contents;
		}
		function arm_bp_get_group_create_button($button_args = array())
		{
			global $arm_access_rules, $current_user;
			if (current_user_can('administrator')) {
				return $button_args;
			}
			$buddypress_rules_options = $arm_access_rules->arm_get_custom_access_rules('buddypress');
                        if(isset($buddypress_rules_options) && !empty($buddypress_rules_options))
                        {
			$bp_add_group = $buddypress_rules_options['buddypress_add_group'];
			$bp_add_group_protection = $bp_add_group['protection'];
			if ($bp_add_group_protection == 1) {
                            $user_id = get_current_user_id();
                            $current_user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                            $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array(-2);
                            
                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids :  array(); 


                            if(!empty($current_user_plan) && is_array($current_user_plan)){
                                foreach($current_user_plan as $cp){
                                    if(in_array($cp, $suspended_plan_ids)){
                                        unset($current_user_plan[array_search($cp,$current_user_plan)]);
                                    }
                                }
                            }
                            
                            $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array(-2);
                            
				$bp_add_group_plans = $bp_add_group['plans'];
				if (!empty($bp_add_group_plans)) {
                                    if(!empty($current_user_plan) && is_array($current_user_plan)){
                                        $return_array = array_intersect($current_user_plan, $bp_add_group_plans);
					if (empty($return_array)) {
						$button_args = array();
					}
                                    }
				} else {
					$button_args = array();
				}
			}
                        }
			return $button_args;
		}
		function arm_bp_get_add_friend_button($button = array())
		{
			global $arm_access_rules, $current_user;
			if (current_user_can('administrator')) {
				return $button;
			}
			$buddypress_rules_options = $arm_access_rules->arm_get_custom_access_rules('buddypress');
                        if(isset($buddypress_rules_options) && !empty($buddypress_rules_options))
                        {
			$bp_friendship = $buddypress_rules_options['buddypress_friendship'];
			$bp_friendship_protection = $bp_friendship['protection'];
			if ($bp_friendship_protection == '1') {
				$user_id = get_current_user_id();
                            $current_user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                            $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array(-2);
                            
                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids :  array(); 

                            if(!empty($current_user_plan) && is_array($current_user_plan)){
                                foreach($current_user_plan as $cp){
                                    if(in_array($cp, $suspended_plan_ids)){
                                        unset($current_user_plan[array_search($cp,$current_user_plan)]);
                                    }
                                }
                            }
                            $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array(-2);
                           
				$bp_friendship_plans = $bp_friendship['plans'];
				if (!empty($bp_friendship_plans)) {
                                    if(!empty($current_user_plan) && is_array($current_user_plan)){
                                        $return_array = array_intersect($current_user_plan, $bp_friendship_plans);
					if (empty($return_array)) {
						$button = array();
					}
                                    }
				} else {
					$button = array();
				}
			}
                        }
			return $button;
		}
		function arm_bp_get_send_message_button_args($button = array())
		{
			global $arm_access_rules, $current_user;
			if (current_user_can('administrator')) {
				return $button;
			}
			$buddypress_rules_options = $arm_access_rules->arm_get_custom_access_rules('buddypress');
                        if(isset($buddypress_rules_options) && !empty($buddypress_rules_options))
                        {
			$bp_priv_msg = $buddypress_rules_options['buddypress_priv_msg'];
			$bp_priv_msg_protection = $bp_priv_msg['protection'];
			if ($bp_priv_msg_protection == 1) {
                            $user_id = get_current_user_id();
                            $current_user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                            $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array(-2);
                            
                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids :  array(); 

                            if(!empty($current_user_plan) && is_array($current_user_plan)){
                                foreach($current_user_plan as $cp){
                                    if(in_array($cp, $suspended_plan_ids)){
                                        unset($current_user_plan[array_search($cp,$current_user_plan)]);
                                    }
                                }
                            }
                            $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array(-2);
				$bp_priv_msg_plans = $bp_priv_msg['plans'];
				if (!empty($bp_priv_msg_plans)) {
                                    if(!empty($current_user_plan) && is_array($current_user_plan)){
                                        $return_array = array_intersect($current_user_plan, $bp_priv_msg_plans);
					if (empty($return_array)) {
						$button = array();
					}
                                    }
				} else {
					$button = array();
				}
			}
                        }
			return $button;
		}
		

                function arm_check_buddypress_pages_access($allowed, $extraVars = array())
		{
			global $arm_access_rules, $current_user;
			if (current_user_can('administrator')) {
				return $allowed;
			}
			if (!function_exists('bp_current_component') || $this->isBuddypressFeature == false) {
				return $allowed;
			}
			$bp_page = bp_current_component();
			$current_page = bp_current_component();
                        
                        $user_id = get_current_user_id();
                        
                        if(is_user_logged_in()){
                                           
                            $current_user_plan = get_user_meta($user_id, 'arm_user_plan_ids', true);
                            $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array(-2);
                            
                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids :  array(); 
                            if(!empty($current_user_plan) && is_array($current_user_plan)){
                                foreach($current_user_plan as $cp){
                                    if(in_array($cp, $suspended_plan_ids)){
                                        unset($current_user_plan[array_search($cp,$current_user_plan)]);
                                    }
                                }
                            }
                            $current_user_plan = !empty($current_user_plan) ? $current_user_plan : array(-2);
                        }
                        else{
                            $current_user_plan = array();
                        }
			$buddypress_rules = $arm_access_rules->arm_get_custom_access_rules('buddypress');
			if ($bp_page != '') {
				if ($allowed && in_array($current_page, array('members', 'profile', 'messages', 'notifications', 'activity', 'friends', 'settings'))) {
					$bp_members_rule = isset($buddypress_rules['buddypress_members']) ? $buddypress_rules['buddypress_members'] : array();
					if (isset($bp_members_rule['protection']) && $bp_members_rule['protection'] == '1') {
						$allowed = false;
                                                $return_array = array_intersect($current_user_plan, $bp_members_rule['plans']);
						if (!empty($bp_members_rule['plans']) && !empty($return_array)) {
							$allowed = true;
						}
					}
				}
				if ($allowed && $current_page == 'groups' && strpos($_SERVER['REQUEST_URI'], '/create/') !== false) {
					$bp_add_group_rule = isset($buddypress_rules['buddypress_add_group']) ? $buddypress_rules['buddypress_add_group'] : array();
					if (isset($bp_add_group_rule['protection']) && $bp_add_group_rule['protection'] == '1') {
						$allowed = false;
                                                $return_array = array_intersect($current_user_plan, $bp_add_group_rule['plans']);
						if (!empty($bp_add_group_rule['plans']) && !empty($return_array)) {
							$allowed = true;
						}
					}
				}
				if ($allowed && $current_page == 'messages' && strpos($_SERVER['REQUEST_URI'], '/compose/') !== false) {
					$bp_priv_msg_rule = isset($buddypress_rules['buddypress_priv_msg']) ? $buddypress_rules['buddypress_priv_msg'] : array();
					if (isset($bp_priv_msg_rule['protection']) && $bp_priv_msg_rule['protection'] == '1') {
						$allowed = false;
                                                $return_array = array_intersect($current_user_plan, $bp_priv_msg_rule['plans']);
						if (!empty($bp_priv_msg_rule['plans']) && !empty($return_array)) {
							$allowed = true;
						}
					}
				}
			}
			return $allowed;
		}

        function arm_check_buddypress_buddyboss(){

        	global $is_bp_active;

            if ($is_bp_active != 1 && is_plugin_active('buddyboss-platform/bp-loader.php') && is_plugin_active('buddypress/bp-loader.php')) {
                return $active_plugin = array('arm_title' => 'BuddyBoss',
                                       'arm_action' => 'buddyboss_options',
                                       'arm_slug' => 'buddyboss',
                                        );   
            } elseif (is_plugin_active('buddypress/bp-loader.php')) {
                return $active_plugin = array('arm_title' => 'BuddyPress', 
                                       'arm_action' => 'buddypress_options',
                                       'arm_slug' => 'buddypress',
                                       );
            }
        }

		function arm_bp_deactivation()
		{
			update_option('arm_is_buddypress_feature', 0);
		}
		function arm_bp_activation()
		{
			$arm_is_buddypress_feature_old = get_option('arm_is_buddypress_feature_old');
			update_option('arm_is_buddypress_feature', $arm_is_buddypress_feature_old);
		}
	}
}
global $arm_buddypress_feature;
$arm_buddypress_feature = new ARM_buddypress_feature();
