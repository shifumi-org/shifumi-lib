<?php 
if (!class_exists('ARM_access_rules'))
{
	class ARM_access_rules
	{
		function __construct()
		{
			global $wpdb, $ARMember, $arm_slugs;

			add_action('wp_ajax_arm_update_access_rules', array($this, 'arm_update_access_rules'));
			add_action('wp_ajax_arm_update_default_access_rules', array($this, 'arm_update_default_access_rules'));

			/* Extend post meta query */
			add_action('posts_where', array($this, 'arm_posts_where_find_in_set'), 10, 2); 
			
			/* Post Meta Box Functions */
			add_action('arm_add_meta_boxes', array($this, 'arm_add_meta_boxes_access_rules'), 10, 3);
                        add_action('wp_insert_post', array($this, 'arm_insert_new_post_action'), 10, 3);
                        //add_action('save_post', array($this, 'arm_save_post_rules'), 20, 3);
                        add_action('created_term', array($this, 'arm_created_term_rules'), 10, 3);
                        add_action('arm_reactivate_plugin',array($this,'arm_install_plugin_data_reactivation'),1000);
            add_filter( 'get_post_metadata', array( $this, 'get_post_meta' ), 11, 4 );
        }

        function get_post_meta( $value, $post_id, $meta_key, $single ){
    		if($meta_key == 'arm_access_plan') {
    			if( isset($value[0]) && is_array($value[0]) ) {  
					$value = !empty($value[0]) ? $value[0] : array();  
				} else if( isset($value[0]) && empty($value[0]) && $value[0] == '' ) {  
					$value = array();  
				}
    		}
    		return $value;
        }
		
		function arm_install_plugin_data_reactivation()
		{
			global $ARMember, $arm_access_rules;
			
			$arm_access_rules->add_rule_data_after_reactivation();
			$ARMember->check_new_users_after_plugin_reactivation();
			
		}
		
		
		function arm_get_default_access_rules()
		{
			global $wpdb, $ARMember, $arm_global_settings;
			$default_access_rules = get_option('arm_default_rules');
			$default_access_rules = maybe_unserialize($default_access_rules);
			return $default_access_rules;
		}
		function arm_update_default_access_rules()
		{
			global $wpdb, $ARMember, $arm_global_settings, $arm_access_rules;
			$response = array('type' => 'error', 'msg' => __('There is a error while updating access rules, please try again.', 'ARMember'));
                        $arm_default_rules = array();
                        $arm_default_rules['arm_allow_content_listing'] = 0;
			if (!empty($_POST['arm_default_rules'])) {
				$arm_default_rules = $_POST['arm_default_rules'];
			}

			$defaultRulesTypes = $arm_access_rules->arm_get_access_rule_types();
			$ruleTypes = array(
                'page' => __('New Pages', 'ARMember'),
                'post' => __('New Posts', 'ARMember'),
                'category' => __('New Categories', 'ARMember'),
                'nav_menu' => __('New Navigation Menus', 'ARMember'),
            );
            if (isset($defaultRulesTypes['post_type']) && !empty($defaultRulesTypes['post_type'])) {
                foreach ($defaultRulesTypes['post_type'] as $postType => $title) {
                    if (!in_array($postType, $ruleTypes)) {
                        $ruleTypes[$postType] = __('New', 'ARMember'). ' '. $title;
                    }
                }
            }
            if (isset($defaultRulesTypes['taxonomy']) && !empty($defaultRulesTypes['taxonomy'])) {
                foreach ($defaultRulesTypes['taxonomy'] as $taxonomy => $title) {
                    if ($taxonomy != 'category') {
                        $ruleTypes[$taxonomy] = __('New', 'ARMember'). ' '. $title;
                    }
                }
            }

            foreach ($ruleTypes as $rtype => $rtitle) {
            	if(isset($_POST['arm_default_restriction_option'][$rtype]))
            	{
            		if($_POST['arm_default_restriction_option'][$rtype]=='-2')
            		{
            			$arm_default_rules[$rtype] = array('-2');
            		}
            		else if($_POST['arm_default_restriction_option'][$rtype]=='1')
            		{
            			if(empty($_POST['arm_default_rules'][$rtype]))
            			{
            				$arm_default_rules_rtype = array();
            			}
            			else {
            				$arm_default_rules_rtype = $_POST['arm_default_rules'][$rtype];
            			}
            			$arm_default_rules[$rtype] = $_POST['arm_default_rules'][$rtype];
            		}
            		else if(empty($_POST['arm_default_restriction_option'][$rtype]))
            		{
            			$arm_default_rules[$rtype] = array();
            		}
            	}
            }

            //$arm_default_rules = maybe_serialize($arm_default_rules);
            update_option('arm_default_rules', $arm_default_rules);
            $response = array('type' => 'success', 'msg' => __('Default Rules Saved Successfully.', 'ARMember'));
			if (isset($_POST['action']) && $_POST['action'] == 'arm_update_default_access_rules') {
				echo json_encode($response);
				die();
			}
		}
		
		/** ****************************************************************************************
		 * Access Rules Data
		 * @since 10Sep2015
		 */
		function arm_get_access_rule_types()
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans;
			$rule_types['post_type'] = array(
				'page' => __('Pages', 'ARMember'),
				'post' => __('Posts', 'ARMember'),
			);
			/* Custom Post Types */
			$custom_post_types = get_post_types(array('public' => true, '_builtin' => false, 'show_ui' => true), 'objects');
			if (!empty($custom_post_types)) {
				foreach ($custom_post_types as $cpt) {
					$rule_types['post_type'][$cpt->name] = $cpt->label;
				}
			}
			/* Tags */
			$rule_types['tags'] = array('post_tag' => __('Tags', 'ARMember'));
			
                 
                        
                        /* Taxonomies */
			$rule_types['taxonomy'] = array('category' => __('Category', 'ARMember'));
			$taxo_args = array('show_ui' => true, 'public' => true, '_builtin' => false);
			$taxonomies = get_taxonomies($taxo_args, 'object');
			if (!empty($taxonomies)) {
				foreach ($taxonomies as $tax) {
						$rule_types['taxonomy'][$tax->name] = $tax->label;
				}
			}
			/* Navigation Menus */
			$menus = get_terms('nav_menu', array('hide_empty' => true));
			if (!empty($menus)) {
				foreach ($menus as $menu) {
					$rule_types['nav_menu'][$menu->slug] = $menu->name;
				}
			}
			/* Other / Custom Rules. */
			$rule_types['other']['special_pages'] = __('Special Pages', 'ARMember');
			/* Hook to add custom rule type options. */
			$custom_rule_types = apply_filters('arm_custom_rule_types', array());
			$rule_types['other'] = array_merge($rule_types['other'], $custom_rule_types);
			return $rule_types;
		}
		function arm_get_custom_access_rules($option_name = '')
		{
			global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
			$custom_rules = get_option('arm_custom_access_rules');
			$custom_rules = maybe_unserialize($custom_rules);
			if (!empty($option_name)) {
				$custom_rules = isset($custom_rules[$option_name]) ? $custom_rules[$option_name] : array();
			}
			return $custom_rules;
		}
		function arm_get_special_pages()
		{
			global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
			$front_type = get_option('show_on_front');
			$front_url = get_home_url();
			if ('page' === $front_type) {
				$home_url = get_permalink(get_option('page_for_posts'));
			} else {
				$home_url = $front_url;
			}
			$arch_year = get_year_link('');
			$arch_month = get_month_link('', '');
			$arch_day = get_day_link('', '', '');
			$arch_hour = esc_url_raw($arm_global_settings->add_query_arg('hour', '15', $arch_day));
			$special_pages = array(
				/* Archive pages */
				'archive' => array('id' => 'archive', 'title' => __('Any Archive page', 'ARMember'), 'url' => ''),
				'author' => array('id' => 'author', 'title' => __('Author Archives', 'ARMember'), 'url' => ''),
				'date' => array('id' => 'date', 'title' => __('Any Date or Time Archive', 'ARMember'), 'url' => ''),
				'year' => array('id' => 'year', 'title' => __('Archive: Year', 'ARMember'), 'url' => $arch_year),
				'month' => array('id' => 'month', 'title' => __('Archive: Month', 'ARMember'), 'url' => $arch_month),
				'day' => array('id' => 'day', 'title' => __('Archive: Day', 'ARMember'), 'url' => $arch_day),
				'time' => array('id' => 'time', 'title' => __('Archive: Time', 'ARMember'), 'url' => $arch_hour),
				/* Singular pages */
				'home' => array('id' => 'home', 'title' => __('Blog Index', 'ARMember'), 'url' => $home_url),
				'notfound' => array('id' => 'notfound', 'title' => __('404 Not Found', 'ARMember'), 'url' => ''),
				'search' => array('id' => 'search', 'title' => __('Search Results', 'ARMember'), 'url' => ''),
				'single' => array('id' => 'single', 'title' => __('Any single page or post', 'ARMember'), 'url' => ''),
				'attachment' => array('id' => 'attachment', 'title' => __('Any attachment page', 'ARMember'), 'url' => ''),
				'feed' => array('id' => 'feed', 'title' => __('Feeds', 'ARMember'), 'url' => ''),
			);
			$sp_setings = $this->arm_get_custom_access_rules('special_pages');
			foreach ($special_pages as $key => $page) {
				$sp_opts = isset($sp_setings[$key]) ? $sp_setings[$key] : array();
				$special_pages[$key]['protection'] = (!empty($sp_opts['protection'])) ? $sp_opts['protection'] : '0';
				$special_pages[$key]['plans'] = (!empty($sp_opts['plans'])) ? $sp_opts['plans'] : array();
			}
			return $special_pages;
		}
		function arm_prepare_rule_data($atts)
		{

			global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_subscription_plans;
			$defaults = array(
				'type' => 'post_type',
				'slug' => 'page',
				'plan' => '',
				'protection' => 'all',
			);
			$args = shortcode_atts($defaults, $atts);
			extract($args);
			$planArr = array();
			if (!empty($plan) && $plan != 'all') {
				$planArr = explode(',', $plan);
			}
			$rule_records = array();
			switch ($type)
			{
				case 'post_type':
					$post_type_obj = get_post_type_object($slug);
					$arm_pages = $arm_global_settings->arm_get_single_global_settings('page_settings');
                                        
					/* Remove Member Directory Page */
					unset($arm_pages['member_profile_page_id']);
					unset($arm_pages['thank_you_page_id']);
					unset($arm_pages['cancel_payment_page_id']);
					if (!empty($post_type_obj))
					{
						$orderby = "ORDER BY P.`post_date` DESC";
						$arm_pages = trim(implode(',', array_filter($arm_pages)), ',');
						if(empty($arm_pages))
						{
							$arm_pages = 0;
						}
						$arm_page_slugs = trim(implode("','", array_filter((array) $arm_slugs)), ",");
						$where = "WHERE P.`post_type`='$slug' AND P.`post_status`='publish' AND P.`ID` NOT IN (". $arm_pages .") AND P.`post_name` NOT IN ('".$arm_page_slugs."') ";
						$join = "";
						if (!empty($planArr)) {
							$findInSet = array();
							foreach ($planArr as $pid) {
								if ($protection == '0') {
									$findInSet[] = " NOT FIND_IN_SET($pid, PM2.`meta_value`) ";
								} else {
									$findInSet[] = " FIND_IN_SET($pid, PM2.`meta_value`) ";
								}
							}
							$findInSet = implode(' OR ', $findInSet);
							$join .= " INNER JOIN `" . $wpdb->postmeta . "` AS PM2 ON PM2.`post_id` = P.`ID`";
							$where .= " AND (PM2.`meta_key`='arm_access_plan' AND ($findInSet))";
						} else {
							if ($protection != 'all') {
								
								if($protection == 1)
								{
									$join .= " INNER JOIN `" . $wpdb->postmeta . "` AS PM1 ON PM1.`post_id` = P.`ID`";
									$where .= " AND (PM1.`meta_key`='arm_access_plan' AND PM1.`meta_value`='0')";
								}
								
							}
						}
						$posts_sql = "SELECT P.`ID`, P.`post_parent`, P.`post_title` FROM `" . $wpdb->posts . "` AS P $join $where $orderby";
						$results = $wpdb->get_results($posts_sql);
						if (!empty($results)) {
							foreach ($results as $p) {
                                                            
                                                            if (is_plugin_active('bbpress/bbpress.php') && class_exists('bbPress')){
                                                                if($slug == 'reply'){
                                                                    $posts_sql1 = "SELECT `post_title`  FROM `" . $wpdb->posts . "` WHERE `ID` = ".$p->post_parent;
                                                                    $post_result = $wpdb->get_row($posts_sql1); 
                                                                    $post_reply_title = $post_result->post_title;
                                                                   
                                                                    $post_title = __('Reply To:','ARMember').$post_reply_title." (<i>#".$p->ID."</i>)";

                                                                }
                                                                else {
                                                                    $post_title = $p->post_title;
                                                                }
                                                            }
                                                            else
                                                            {
                                                                $post_title = $p->post_title;
                                                            }
								$item_plans = get_post_meta($p->ID, 'arm_access_plan');
								$item_plans = (!empty($item_plans)) ? $item_plans : array();
								
								if(count($item_plans) == 0)
									$protect = 0;
								else
									$protect = 1;
								
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
									$rule_records[$p->ID] = array(
										'id' => $p->ID,
										'title' => $post_title,
										'protection' => $protect,
										'plans' => $item_plans,
									);
								}
							}
						}
					}
					break;
				case 'tags':
				$taxonomy = get_taxonomy($slug);
				if (!empty($taxonomy))
				{
						$terms_args = array(
							'hide_empty' => false,
							'parent' => 0,
							'orderby' => 'name',
							'order' => 'ASC',
							'taxonomy' => $slug
							);
						$terms_ = get_categories($terms_args);
						if( !empty($terms_) ){
							foreach( $terms_ as $key => $pterm ){
								$protect = get_arm_term_meta($pterm->term_id, 'arm_protection', true);
								$protect = (!empty($protect)) ? $protect : 0;

								$item_plans = get_arm_term_meta($pterm->term_id, 'arm_access_plan');
								$item_plans = (!empty($item_plans)) ? $item_plans : array();
								
									
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
								
							}
						}
					}
                                case 'taxonomy':
				$taxonomy = get_taxonomy($slug);
				if (!empty($taxonomy))
				{
						
						$terms_args = array(
							'hide_empty' => false,
							'parent' => 0,
							'orderby' => 'name',
							'order' => 'ASC',
							'taxonomy' => $slug
							);
						$terms_ = get_categories($terms_args);
						if( !empty($terms_) ){
							foreach( $terms_ as $key => $pterm ){
								$protect = get_arm_term_meta($pterm->term_id, 'arm_protection', true);
								$protect = (!empty($protect)) ? $protect : 0;

								$item_plans = get_arm_term_meta($pterm->term_id, 'arm_access_plan');
								$item_plans = (!empty($item_plans)) ? $item_plans : array();
								
									
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
									$rule_records[$pterm->term_id] = array(
										'id' => $pterm->term_id,
										'title' => $pterm->name,
										'protection' => $protect,
										'plans' => $item_plans,
										);
									$rule_records = $this->arm_get_sub_categories($rule_records,$pterm->term_id,$planArr,$slug,$protection);
								}
							}
						}
					}
					break;
				case 'nav_menu':
					$menu = wp_get_nav_menu_object($slug);
					if (!empty($menu))
					{
						$menu_items = wp_get_nav_menu_items($menu->term_id);
						if (!empty($menu_items)) {
							foreach ($menu_items as $item) {
                                if ($item->menu_item_parent > 0) {
                                    continue;
                                }
								$item_plans = get_post_meta($item->ID, 'arm_access_plan');
								$item_plans = (!empty($item_plans)) ? $item_plans : array();
								
								if(count($item_plans) == 0)
									$protect = 0;
								else
									$protect = 1;
								
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
									$rule_records[$item->ID] = array(
										'id' => $item->ID,
										'title' => $item->title,
										'protection' => $protect,
										'plans' => $item_plans,
									);
                                    $rule_records = $this->arm_get_sub_nav_menu($rule_records, $item->ID, $planArr, $slug, $protection);
								}
							}
						}
					}
					break;
				case 'other':
					if ($slug == 'special_pages')
					{
						$special_pages = $this->arm_get_special_pages();
						if (!empty($special_pages)) {
							foreach ($special_pages as $key => $sp) {
								$protect = $sp['protection'];
								$protect = (!empty($protect)) ? $protect : 0;
								$item_plans = (!empty($sp['plans'])) ? $sp['plans'] : array();
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
									$rule_records[$sp['id']] = $sp;
								}
							}
						}
					} else {
						$rule_records = apply_filters('arm_prepare_custom_rule_data', $rule_records, $args);
					}
					break;
				default:
					break;
			}
			$rule_records = apply_filters('arm_prepare_rule_data', $rule_records, $args);
			return $rule_records;
		}
		function arm_update_access_rules($posted_data=array())
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_capabilities_global;
			$insert_plan = array() ; $remove_plan = array(); $remove_all_plan = array(); $remove_all_plan2 = array(); $insert_protection_meta = array(); $update_protection_meta = array(); $all_taxonomies = array(); $update_main_protection_meta = array();
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_access_rules'], '1');

			$remove_plan_string = "Delete From ".$wpdb->prefix."postmeta where post_id = ";
			$insert_plan_string = "Insert into ".$wpdb->prefix."postmeta (post_id,meta_key,meta_value) VALUES ";

			$update_plan_string = "UPDATE ".$wpdb->prefix."postmeta set meta_value = 0 WHERE meta_key = 'arm_access_plan' AND post_id = ";
			$update_term_string = "UPDATE ".$ARMember->tbl_arm_termmeta." set meta_value = 1 WHERE meta_key = 'arm_protection' AND arm_term_id = ";
			
			$update_main_term_string = "UPDATE ".$ARMember->tbl_arm_termmeta." set meta_value = 0 WHERE meta_key = 'arm_protection' AND arm_term_id = ";
			
			$remove_term_string = "Delete From ".$ARMember->tbl_arm_termmeta." where arm_term_id = ";
			$insert_term_string = "Insert into ".$ARMember->tbl_arm_termmeta." (arm_term_id,meta_key,meta_value) VALUES ";
			
			$insert_meta_string = "";
            $arm_global_settings->arm_set_ini_for_access_rules();
			set_time_limit(0);
			$posted_data = (isset($_POST) && !empty($_POST)) ? $_POST : $posted_data;
			$response = array('status' => 'error', 'message' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
			$rule_type = sanitize_text_field($posted_data['type']);
			$type_slug = sanitize_text_field($posted_data['slug']);
			$arm_rules = json_decode(stripslashes($posted_data['form_data']));

			$arm_original_rules = json_decode(stripslashes($posted_data['form_data_original']));

			if (!empty($arm_rules))
			{
				if ($rule_type == 'other')
				{
					$custom_rules = $this->arm_get_custom_access_rules();
					if ($type_slug == 'special_pages')
					{
						foreach ($arm_rules as $item_id => $item_rule) {
							$item_rule = (array) $item_rule;
							if (empty($item_rule['protection']) || $item_rule['protection'] == '0') {
								unset($item_rule['plans']);
							} else {
								$item_rule['plans'] = (array) $item_rule['plans'];
								$item_rule['plans'] = array_keys($item_rule['plans']);
							}
							$custom_rules['special_pages'][$item_id] = $item_rule;
						}
					}
					$custom_rules = apply_filters('arm_before_update_custom_access_rules', $custom_rules, $type_slug, $arm_rules);
					update_option('arm_custom_access_rules', $custom_rules);
				}

				else
				{
                                
					foreach ($arm_rules as $item_id => $item_rule) {
						
						$item_rule = (array) $item_rule;
						
						if($rule_type == 'taxonomy' || $rule_type == 'tags')
						{
							$all_taxonomies[] = $item_id;
						}
						
						
						if (!empty($item_rule)) {
							if($item_rule['protection'] == '0')
							{
								if ($rule_type == 'post_type' || $rule_type == 'nav_menu')
								{
									$remove_all_plan[] = " $item_id AND meta_key = 'arm_access_plan'";
									$remove_all_plan2[] = " $item_id AND meta_key = 'arm_protection'";
								}
								else if($rule_type == 'taxonomy' || $rule_type == 'tags')
								{
									$update_main_protection_meta[] = $item_id;
									$remove_all_plan[] = " $item_id AND meta_key = 'arm_access_plan'";
									$insert_plan[] = "('".$item_id."','arm_access_plan','0')";
								}
							}
							else
							{
								if ($rule_type == 'post_type' || $rule_type == 'nav_menu')
								{
									$old_record = $wpdb->get_var("SELECT `meta_id` FROM ".$wpdb->prefix."postmeta WHERE `post_id`='".$item_id."' AND `meta_key` = 'arm_access_plan'");
								}
								else if($rule_type == 'taxonomy' || $rule_type == 'tags')
								{
									$old_record = $wpdb->get_var("SELECT `meta_id` FROM ".$ARMember->tbl_arm_termmeta." WHERE `arm_term_id`='".$item_id."' AND `meta_key` = 'arm_protection'");
								}
                                                                
                                                                
                                                                
                                                                
								if ($old_record != null)
								{
									if($rule_type == 'taxonomy' || $rule_type == 'tags')
										$update_protection_meta[] = $item_id;
									
								}
								else
								{
									if ($rule_type == 'post_type' || $rule_type == 'nav_menu')
									{
										$insert_protection_meta[] = "('".$item_id."','arm_access_plan','0')";
									}
									
								}

								$item_rule['plans'] = (array) $item_rule['plans'];
								$item_rule['plans'] = array_keys($item_rule['plans']);

								$original_plan_arr = array();
								$myplans = array();
								$original_plan_arr = (array) $arm_original_rules->$item_id->plans;
								$original_plan_arr = array_keys($original_plan_arr);

								$myplans = $item_rule['plans'];
								
								foreach($item_rule['plans'] as $key=>$value)
								{
									if($value > 0 || $value == '-2')
									{
									if(!in_array($value,$original_plan_arr))
									{
									    
										$insert_plan[] = "('".$item_id."','arm_access_plan','".$value."')";
									}
									}
								}
								
								foreach($original_plan_arr as $key=>$value)
								{
									if($value > 0 || $value == '-2')
									{
									if(!in_array($value,$myplans))
									{
										$remove_plan[] = " $item_id AND meta_key = 'arm_access_plan' AND meta_value = ".$value;
									}
									}
								}
							}
						}
						

					}
				}

				if ($rule_type == 'post_type' || $rule_type == 'nav_menu') 
				{
					global $wpdb;
					foreach($remove_all_plan as $key=>$value)
					{
						$del_query = $wpdb->query($remove_plan_string.$value);
						$del_query = $wpdb->query($remove_plan_string.$remove_all_plan2[$key]);
					}
					foreach($remove_plan as $key=>$value)
					{
						$del_query = $wpdb->query($remove_plan_string.$value);
					}

					foreach($insert_protection_meta as $key=>$value)
					{
						$ins_query = $wpdb->query($insert_plan_string.$value);
						
					}
					
					foreach($update_protection_meta as $key=>$value)
					{
						$ins_query = $wpdb->query($update_plan_string.$value);
					}
					
					$total_ins_query = count($insert_plan);
					
					if($total_ins_query > 100)
					{
						$all_values_string = "";
						$i = 1; $j=1;	
						foreach($insert_plan as $key=>$value)
						{
							
                            if($i == 100 || $j == $total_ins_query)	
							 $all_values_string.= " ".$value." ";
						    else
						         $all_values_string.= " ".$value." ,";
							
							if($i == 100)
							{
								$ins_query = $wpdb->query($insert_plan_string.$all_values_string);
								$i = 0;
								$all_values_string = "";
								
							}
							$i ++;
                                                        $j ++;
						}
						
						if($all_values_string != "")
						{
							$ins_query = $wpdb->query($insert_plan_string.$all_values_string);
						}
						
					}
					else
					{
						foreach($insert_plan as $key=>$value)
						{
							$ins_query = $wpdb->query($insert_plan_string.$value);
						}
					}
				}
				else if ($rule_type == 'taxonomy' || $rule_type == 'tags') 
				{
					global $wpdb;
					foreach($remove_all_plan as $key=>$value)
					{
						$del_query = $wpdb->query($remove_term_string.$value);
					}
					foreach($remove_plan as $key=>$value)
					{
						$del_query = $wpdb->query($remove_term_string.$value);
					}
					
					foreach($update_main_protection_meta as $key=>$value)
					{
						$ins_query = $wpdb->query($update_main_term_string.$value);
						
					}
					foreach($insert_protection_meta as $key=>$value)
					{
						$ins_query = $wpdb->query($insert_term_string.$value);
						
					}
					
					foreach($update_protection_meta as $key=>$value)
					{
						$ins_query = $wpdb->query($update_term_string.$value);
						
					}
					
					$total_ins_query = count($insert_plan);
					
					if($total_ins_query > 100)
					{
						$all_values_string = "";
						$i = 1;	$j=1;
						foreach($insert_plan as $key=>$value)
						{
						  
						   if($i == 100 || $j == $total_ins_query)	
							 $all_values_string.= " ".$value." ";
						   else
						     $all_values_string.= " ".$value." ,";
							
													
							if($i == 100)
							{
								$ins_query = $wpdb->query($insert_term_string.$all_values_string);
								$i = 0;
								$all_values_string = "";
								
							}
							$i ++;
							$j++;
						}
						
						if($all_values_string != "")
						{
							$ins_query = $wpdb->query($insert_term_string.$all_values_string);
						}
						
					}
					else
					{
						foreach($insert_plan as $key=>$value)
						{
							$ins_query = $wpdb->query($insert_term_string.$value);
						}
					}
					
					
					foreach($all_taxonomies as $key=>$value)
					{
						$old_record = $wpdb->get_results("SELECT * FROM ".$ARMember->tbl_arm_termmeta." WHERE `arm_term_id`='".$value."' AND `meta_key` = 'arm_access_plan'");
						if( count($old_record) > 0 )
						{
							$meta_val = array();
							if(is_array($old_record) && count($old_record) > 1)
							{
								foreach($old_record as $currrec)
								{
									$meta_val[] = $currrec->meta_value;
								}
							}
								
							if(is_array($meta_val) && count($meta_val)>0 && in_array('0',$meta_val))
							{
								$remove_term_string_meta = "Delete From ".$ARMember->tbl_arm_termmeta." where arm_term_id = '".$value."' AND `meta_key` = 'arm_access_plan' AND meta_value = 0";
								$remove_term_string_meta_query = $wpdb->query($remove_term_string_meta);
							}
							
						}
						else
						{
							$insert_blank_meta = "('".$value."','arm_access_plan','0')";
							$ins_query = $wpdb->query($insert_term_string.$insert_blank_meta);
						}
						
					}
					

				}
				$response = array('type' => 'success', 'msg' => __('Access Rules Updated Successfully.', 'ARMember'));
				do_action( 'arm_update_access_rules_from_outside', $posted_data );
			}
			
			if (isset($posted_data['action']) && $posted_data['action'] == 'arm_update_access_rules') {
				echo json_encode($response);
				exit;
			} else {
				return $response;
			}
		}
                
                function install_redirection_settings(){
                    
                    global $arm_global_settings;
                     
                     
                     $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                     $page_settings = $all_global_settings['page_settings'];
                     $edit_profile_page_id = isset($page_settings['edit_profile_page_id']) ? $page_settings['edit_profile_page_id'] : 0;
                     $arm_redirection_settings['signup']['redirect_type'] = 'common';
                     $arm_redirection_settings['signup']['type'] = 'page';
                     
                     $arm_redirection_settings['signup']['page_id'] = $edit_profile_page_id;
                     $arm_redirection_settings['signup']['url'] = ARM_HOME_URL;
                     $arm_redirection_settings['signup']['refferel'] = ARM_HOME_URL; 
                   
                     $arm_redirection_settings['signup']['redirect_type'] = 'common';
                    $arm_redirection_settings['signup']['conditional_redirect'][] = array('form_id' => 0,
                       'url' => ARM_HOME_URL);
                    $arm_redirection_settings['login']['main_type'] = 'fixed';
                    $arm_redirection_settings['login']['type'] = 'page';
                    $arm_redirection_settings['login']['page_id'] = $edit_profile_page_id;
                    $arm_redirection_settings['login']['url'] = ARM_HOME_URL;
                    $arm_redirection_settings['login']['refferel'] = ARM_HOME_URL;
                    $arm_redirection_settings['login']['conditional_redirect'][] = array('plan_id' => 0,
                                        'condition' => '',
                                        'expire' => 0,
                                        'url' => ARM_HOME_URL);
                    $arm_redirection_settings['login']['conditional_redirect']['default'] = ARM_HOME_URL;
                    
                    
                    $globalSettings = $arm_global_settings->global_settings;
                    $ty_pageid = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
                    $arm_redirection_settings['setup_signup']['type'] = $arm_redirection_settings['setup_change']['type'] = $arm_redirection_settings['setup_renew']['type'] = 'page';
                    $arm_redirection_settings['setup_signup']['page_id']= $arm_redirection_settings['setup_change']['page_id'] = $arm_redirection_settings['setup_renew']['page_id'] = $ty_pageid;
                    $arm_redirection_settings['setup_signup']['url'] =  $arm_redirection_settings['setup_change']['url'] = $arm_redirection_settings['setup_renew']['url'] = ARM_HOME_URL;
                    $redirection_settings['setup']['default'] = ARM_HOME_URL;
                       
                        $arm_redirection_settings['social']['type'] = 'page';
                        $arm_redirection_settings['social']['page_id'] = $edit_profile_page_id;
                        $arm_redirection_settings['social']['url'] = ARM_HOME_URL;
                        $arm_redirection_settings['default_access_rules']['logged_in']['type'] =  'home';
                        $arm_redirection_settings['default_access_rules']['blocked']['type'] =  'home';
                        //$arm_redirection_settings['default_access_rules']['pending']['type'] =  'home';
                        $arm_redirection_settings['default_access_rules']['drip']['type'] =  'home';
                        $arm_redirection_settings['default_access_rules']['non_logged_in']['type'] = 'home';

                        $arm_redirection_settings['default_access_rules']['non_logged_in']['redirect_to'] = '';
                        $arm_redirection_settings['default_access_rules']['logged_in']['redirect_to'] =  '';
                        $arm_redirection_settings['default_access_rules']['blocked']['redirect_to'] =  '';
                        //$arm_redirection_settings['default_access_rules']['pending']['redirect_to'] =  '';
                        $arm_redirection_settings['default_access_rules']['drip']['redirect_to'] =  '';

                        //$arm_redirection_settings = maybe_serialize($arm_redirection_settings);
                        update_option('arm_redirection_settings', $arm_redirection_settings);
                }
                
		/**
		 * Set Access Rule for `post_type`, `taxonomy` & `nav_menu`
		 */
		function arm_set_item_rule($rule_type = '', $type_slug = '', $item_id = 0, $protect = 1, $item_plans = array())
		{
                    
                  
			global $wpdb, $ARMember;
			if (!empty($item_id) && $item_id != 0)
			{
				$protect = ($protect == 1) ? true : false;
				switch ($rule_type)
				{
					case 'post_type':
					case 'nav_menu':
						if ($protect) {
							
							update_post_meta($item_id, 'arm_access_plan', '0');
							
							
							
							if( !empty($item_plans) ){
								foreach( $item_plans as $key => $new_plan ){
									/* Check if post meta already exists with plan */
									$check_exists_post_meta = $wpdb->get_results($wpdb->prepare("SELECT COUNT(*) as total FROM `".$wpdb->prefix."postmeta` WHERE post_id = %d AND meta_key = %s AND meta_value = %d",$item_id,'arm_access_plan',$new_plan));
									if( $check_exists_post_meta[0]->total > 0 ){
										/* Do Nothing */
									} else {
										delete_post_meta($item_id,'arm_access_plan', '0' );
										add_post_meta($item_id, 'arm_access_plan', $new_plan);
									}
								}
							}
						}
						return true;
						break;
					case 'taxonomy':
						if ($protect) {
                            update_metadata('arm_term',$item_id,'arm_protection','1');
							
							
							if( !empty($item_plans) ){
								foreach( $item_plans as $key => $plan ){
									/* check if texonomy meta deta is already exists */
									$check_exists_term_meta = $wpdb->get_results($wpdb->prepare("SELECT COUNT(*) as total FROM `".$ARMember->tbl_arm_termmeta."` WHERE arm_term_id = %d AND meta_key = %s AND meta_value = %d",$item_id,'arm_access_plan',$plan));
									
									if( $check_exists_term_meta[0]->total > 0 ){
										/* Do Nothing */
									} else {
										$wpdb->query( $wpdb->prepare( "DELETE FROM `".$ARMember->tbl_arm_termmeta."` WHERE arm_term_id = %d AND meta_key = %s AND meta_value = %s",$item_id,'arm_access_plan','0' ) );
										$insert = $wpdb->query( $wpdb->prepare("INSERT INTO `".$ARMember->tbl_arm_termmeta."` (arm_term_id,meta_key,meta_value) VALUES (%d,%s,%d)",$item_id,'arm_access_plan',$plan ));
									}
								}
							}
						} else {
                            update_metadata('arm_term',$item_id,'arm_protection','0');
                            update_metadata('arm_term',$item_id,'arm_access_plan','0');
						}
						return true;
						break;
					case 'other':
						$custom_rules = $this->arm_get_custom_access_rules();
						$item_rule = array();
						if ($protect) {
							$item_rule['protection'] = '1';
							$item_rule['plans'] = (array) $item_plans;
							$custom_rules[$type_slug][$item_id] = $item_rule;
							update_option('arm_custom_access_rules', $custom_rules);
						} else {
							$item_rule['protection'] = '0';
							$item_rule['plans'] = array();
							$custom_rules[$type_slug][$item_id] = $item_rule;
							update_option('arm_custom_access_rules', $custom_rules);
						}
						return true;
						break;
					default :
						break;
				}
			}
			return false;
		}
		function arm_inherit_plan_rules($plan_id = 0, $inherit_plan_id = 0)
		{
			global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
			if (!empty($plan_id) && $plan_id != 0 && !empty($inherit_plan_id) && $inherit_plan_id != 0)
			{
				$rule_types = $this->arm_get_access_rule_types();
				$all_rules_data = array();
				if (!empty($rule_types))
				{
					$i = 0;
					foreach ($rule_types as $rule_type => $options) {
						if (!empty($options)) {
							foreach ($options as $slug => $val) {
								$rule_slug_data = $this->arm_prepare_rule_data(array(
									'type' => $rule_type,
									'slug' => $slug,
									'plan' => $inherit_plan_id,
									'protection' => '1',
								));
								if (!empty($rule_slug_data)) {
									if ($rule_type == 'other') {
										$custom_rules = $this->arm_get_custom_access_rules();
										if ($slug == 'special_pages') {
											foreach ($rule_slug_data as $item_id => $item) {
												$item_rule['protection'] = $item['protection'];
												$item_rule['plans'] = (array) $item['plans']; 
												$item_rule['plans'][] = $plan_id;
												
												$custom_rules['special_pages'][$item_id] = $item_rule;
											}
										}
										$custom_rules = apply_filters('arm_before_update_custom_access_rules', $custom_rules, $slug, array());
										update_option('arm_custom_access_rules', $custom_rules);
									} else {
										foreach ($rule_slug_data as $item) {
											$item_plans = (array) $item['plans']; 
											$item_plans[] = $plan_id;
											$this->arm_set_item_rule($rule_type, $slug, $item['id'], $item['protection'], $item_plans);
											$i++;
											if ($i >= 1000) {
												$i = 0; sleep(1);
											}
										}
									}
									$all_rules_data[$rule_type][$slug] = $rule_slug_data;
								}/* End `if (!empty($rule_slug_data))` */
							}/* End `foreach ($options as $slug => $val)` */
						}/* End `if (!empty($options))` */
					}/* End `foreach ($rule_types as $rule_type => $options)` */
				}/* End `if (!empty($rule_types))` */
			}
			return;
		}
		function install_rule_data()
		{
			global $wp, $wpdb, $arm_errors, $ARMember, $arm_global_settings;
            $arm_global_settings->arm_set_ini_for_access_rules();
            /* For Taxonomies */
            $taxo_args = array('show_ui' => true, 'public' => true);
            $taxonomies = get_taxonomies($taxo_args, 'object');
            if (!empty($taxonomies)) {
                foreach ($taxonomies as $tax) {
                        $terms = get_terms($tax->name, array('hide_empty' => false));
                        if (!empty($terms)) {
                            foreach ($terms as $term) {
                                if (!get_arm_term_meta($term->term_id, 'arm_protection', true)) {
                                
                                update_metadata('arm_term',$term->term_id,'arm_protection','0');
                                
                                update_metadata('arm_term',$term->term_id,'arm_access_plan','0');
                                }
                            }
                        }
                        $terms = null;
                }
                $taxonomies = $tax = null;
            }

            

            
        }
        
        /** ***************************************************************************************
         *Add Posts and Tax access rules after plugin reactivation starts  
         */
        function add_rule_data_after_reactivation()
        {
            global $wpdb, $arm_global_settings,$ARMember;
            $arm_global_settings->arm_set_ini_for_access_rules();
			
            /**
             * Query For Term Taxonomies. */
             
            $allTermsMeta = $wpdb->get_results("SELECT `arm_term_id` FROM `{$ARMember->tbl_arm_termmeta}` WHERE `meta_key` = 'arm_protection'", ARRAY_A);
            $remainigTermsWhere = "";
            if (!empty($allTermsMeta)) {
                $termIds = array();
                foreach ($allTermsMeta as $tMeta) {
                    $termIds[] = $tMeta['arm_term_id'];
                }
                $remainigTermsWhere = "WHERE `term_id` NOT IN ('" . implode("','", $termIds) . "')";
                $allTermsMeta = $termIds = $tMeta = null;
            }
            $remainigTerms = $wpdb->get_results("SELECT * FROM `{$wpdb->term_taxonomy}` {$remainigTermsWhere}", ARRAY_A);
            if (!empty($remainigTerms)) {
                foreach ($remainigTerms as $termVal) {
                    $taxonomyInfo = get_taxonomy($termVal['taxonomy']);
                    update_metadata('arm_term',$termVal['term_id'],'arm_protection','0');
                    
                    update_metadata('arm_term',$termVal['term_id'],'arm_access_plan','0');
                    $taxonomyInfo = null;
                }
            }
            $remainigTerms = $remainigTermsWhere = $termVal = null;
            /**
             * Query For Post Types. */

           
            
        }
        /** ***************************************************************************************
		 * Add Custom Metaboxes For Access Rules
		 */
		function arm_add_meta_boxes_access_rules($screen = '', $arm_context = 'side', $arm_priority = 'high')
		{
			global $wpdb, $pagenow, $ARMember, $arm_global_settings, $arm_access_rules;
			if (current_user_can('administrator') || current_user_can('arm_content_access_rules_metabox')) {
				if (!empty($screen)) {
					$arm_context = (!empty($arm_context)) ? 'side' : '';
					$arm_priority = (!empty($arm_priority)) ? 'high' : '';
					add_meta_box('arm_membership_access_id', __('ARMember Access Rules', 'ARMember'), array(&$this, 'arm_rule_plan_metabox_callback'), $screen, $arm_context, $arm_priority);
				}
			}
			return;
		}
		function arm_rule_plan_metabox_callback($post)
		{
			global $wpdb, $pagenow, $ARMember, $arm_global_settings, $arm_subscription_plans;
			$checked_all = "checked='checked'";
			$post_plans = array();
			$post_protection = '0';
			if (in_array($pagenow, array('post-new.php')) && !empty($post->post_type)) {
				$default_rules = $this->arm_get_default_access_rules();
				if (isset($default_rules[$post->post_type]) && !empty($default_rules[$post->post_type])) {
					$post_protection = '1';
					$post_plans = $default_rules[$post->post_type];
				}
			}
			if (in_array($pagenow, array('post.php'))) {
						
				$post_plans = get_post_meta($post->ID, 'arm_access_plan');

				$post_plans = !empty($post_plans) ? $post_plans : array();
				
				if(count($post_plans) == 0)
							$post_protection = 0;
						else
							$post_protection = 1;
				
				
			}
			$metabox_content = '<div class="arm_rules_metabox_container arm_post_metabox_container '.(is_rtl() ? 'arm_rtl_wrapper' : '').'">';
			$metabox_content .= '<h4 class="arm_metabox_label">' . __('Default Restriction', 'ARMember') . ':</h4>';
			$metabox_content .= '<input type="hiddden" value="' . $post_protection . '" name="arm_rule[protection]" id="rule_protection_checkbox_hidden" style="display:none;"/>';
			$metabox_content .= '<div class="armswitch armswitchbig">';
			$metabox_content .= '<input type="checkbox" value="1" class="armswitch_input" id="rule_protection_checkbox" ' . checked($post_protection, '1', false) . '/>';
			$metabox_content .= '<label for="rule_protection_checkbox" class="armswitch_label"></label>';
			$metabox_content .= '<div class="armclear"></div>';
			$metabox_content .= '</div>';
			$metabox_content .= '<div>'.__('If Default Restriction is ON than ONLY below selected plan members can access this page.', 'ARMember').'</div>';
			$metabox_content .= '<div class="arm_rule_plan_checkboxes">';
			$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
			if (!empty($all_plans)) {
				$metabox_content .= '<h4 class="arm_metabox_label">' . __('Membership Plans', 'ARMember') . ':</h4>';
                                $plan_id = -2;
                                $plan_checked = (in_array($plan_id, $post_plans)) ? 'checked="checked"' : '';
                                $metabox_content .= '<div class="arm_plan_switch">';
                                $metabox_content .= '<div class="armswitch">';
                                $metabox_content .= '<input type="checkbox" value="' . $plan_id . '" name="arm_rule[plans][]" class="armswitch_input arm_rule_plan_checkbox" id="arm_rule_plan_checkbox_' . $plan_id . '" ' . $plan_checked . '/>';
                                $metabox_content .= '<label class="armswitch_label" for="arm_rule_plan_checkbox_' . $plan_id . '"></label>';
                                $metabox_content .= '</div>';
                                $metabox_content .= '<label for="arm_rule_plan_checkbox_' . $plan_id . '" class="arm_post_switch_label">'.__('Users Having No Plan','ARMember').'</label>';
                                $metabox_content .= '</div>';
				foreach ($all_plans as $plan) {
					$plan_id = $plan['arm_subscription_plan_id'];
					$plan_checked = (in_array($plan_id, $post_plans)) ? 'checked="checked"' : '';
					$metabox_content .= '<div class="arm_plan_switch">';
					$metabox_content .= '<div class="armswitch">';
					$metabox_content .= '<input type="checkbox" value="' . $plan_id . '" name="arm_rule[plans][]" class="armswitch_input arm_rule_plan_checkbox" id="arm_rule_plan_checkbox_' . $plan_id . '" ' . $plan_checked . '/>';
					$metabox_content .= '<label class="armswitch_label" for="arm_rule_plan_checkbox_' . $plan_id . '"></label>';
					$metabox_content .= '</div>';
					$metabox_content .= '<label for="arm_rule_plan_checkbox_' . $plan_id . '" class="arm_post_switch_label">' . stripslashes($plan['arm_subscription_plan_name']) . '</label>';
					$metabox_content .= '</div>';
				}
			}
			$metabox_content .= '</div>';
			$metabox_content .= '</div>';
			echo $metabox_content;
			?>
			<script type="text/javascript">
			jQuery(document).on('change', '#rule_protection_checkbox', function () {
				if (jQuery(this).is(':checked')) {
					jQuery('#rule_protection_checkbox_hidden').val('1');
				} else {
					jQuery('#rule_protection_checkbox_hidden').val('0');
                                        jQuery('.arm_rule_plan_checkbox').prop('checked', false);
				}
			});
			jQuery(document).on('click', '.arm_rule_plan_checkbox', function () {				
				if (jQuery(this).is(':checked')) {
					if (!jQuery('#rule_protection_checkbox').is(':checked')) {
						jQuery('#rule_protection_checkbox').trigger('click');
					}
				} else {
					if (jQuery('input.arm_rule_plan_checkbox:checked').length == 0) {
						if (jQuery('#rule_protection_checkbox').is(':checked')) {
							//jQuery('#rule_protection_checkbox').trigger('click');
						}
					}
				}
			});
			</script>
			<?php
		}
        function arm_insert_new_post_action($post_id, $post, $update=false)
        {
            global $wpdb, $pagenow, $ARMember, $arm_global_settings, $arm_subscription_plans;
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
            if (!(current_user_can('administrator'))) {
                if (wp_is_post_revision($post_id)) {
                    return;
                }
				
                $post_type = get_post_type($post_id);
                if (!$update) {
                    $default_rules = $this->arm_get_default_access_rules();
                    if (!empty($post_type) && $post_type != 'nav_menu_item') {
                        if (!empty($default_rules[$post_type])) {
                            $access_plans = trim(implode(',', $default_rules[$post_type]), ',');
                            
							update_post_meta($post_id, 'arm_access_plan', '0');
                            
                            foreach( $default_rules[$post_type] as $key => $access_plan ){
                            	add_post_meta($post_id,'arm_access_plan',$access_plan);
                            }
                        }
                    }
                }
			}
			return;
        }
		function arm_save_post_rules($post_id, $post, $update=false)
		{
			global $wpdb, $pagenow, $ARMember, $arm_global_settings, $arm_subscription_plans;
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}
			if (current_user_can('administrator') || current_user_can('arm_content_access_rules_metabox')) {
				$default_rules = $this->arm_get_default_access_rules();
				if (!$update) {
					if (isset($post->post_type) && $post->post_type == 'nav_menu_item') {
						if (!empty($default_rules['nav_menu'])) {
							$access_plans = trim(implode(',', $default_rules['nav_menu']), ',');
							
							delete_post_meta($post_id, 'arm_access_plan','0');
							update_post_meta($post_id, 'arm_access_plan','0');
							foreach( $default_rules['nav_menu'] as $key => $access_plan ){
								/*add_post_meta($post_id,'arm_access_plan',$access_plan);*/
								$wpdb->query("INSERT INTO ".$wpdb->prefix."postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ($post_id, 'arm_access_plan', $access_plan)");
							}
						} else {
							$wpdb->query( $wpdb->prepare("DELETE FROM `".$wpdb->prefix."postmeta` WHERE post_id = %d AND meta_key IN (%s,%s)",$post_id,'arm_protection','arm_access_plan') );
						}
						return;
					} else {
						$wpdb->query( $wpdb->prepare("DELETE FROM `".$wpdb->prefix."postmeta` WHERE post_id = %d AND meta_key IN (%s,%s)",$post_id,'arm_protection','arm_access_plan') );
					}
				}
				if (!isset($_POST['arm_rule']) && ( empty($_REQUEST['page']) || (!empty($_REQUEST['page']) && ($_REQUEST['page'] != "pmxi-admin-import")) ) && (empty($_POST['fetch_attachments']))) {
					//Special condition for WP All Import plugin.
					return;
				}
				$protection = isset($_POST['arm_rule']['protection']) ? $_POST['arm_rule']['protection'] : 0;
				$plans = '';                        
				if ($protection == '1') {
					$plans = !empty($_POST['arm_rule']['plans']) ? trim(implode(',', $_POST['arm_rule']['plans']), ',') : '';
				} else {
					/* Delete Post meta if no any plan assigned to post or page */
					$wpdb->query( $wpdb->prepare("DELETE FROM `".$wpdb->prefix."postmeta` WHERE post_id = %d AND meta_key IN (%s,%s)",$post_id,'arm_protection','arm_access_plan') );
				}
				delete_post_meta($post_id,'arm_access_plan');
				
				if ($protection == '1') {
				                        /*add_post_meta($post_id, 'arm_access_plan','0');*/
				                        $wpdb->query("INSERT INTO ".$wpdb->prefix."postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ($post_id, 'arm_access_plan', '0')");
						}
				

				$plans = apply_filters( 'arm_modify_restriction_plans_outside', $plans, $post_id );


				if((!empty($_REQUEST['page']) && ($_REQUEST['page'] == "pmxi-admin-import")) || !empty($_POST['fetch_attachments']))
				{
					$default_rules_post_post_type = !empty($default_rules[$post->post_type]) ? $default_rules[$post->post_type] : array();
					if(!empty($default_rules_post_post_type))
					{
						foreach($default_rules_post_post_type as $default_rule_key => $default_rule_val)
						{
							$plans .= ','.$default_rule_val;
						}
					}
				}

				if( $plans != '' ){
					$all_plans = explode(',',$plans );
	                           
					foreach( $all_plans as $key => $plan ){
	                                    /*add_post_meta($post_id, 'arm_access_plan',$plan);*/
	                                    $wpdb->query("INSERT INTO ".$wpdb->prefix."postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ($post_id, 'arm_access_plan', $plan)");
	                                    
	                          
					}
	                                
				}
			}
			return;
		}
		function arm_created_term_rules($term_id, $tt_id, $taxonomy)
		{
			global $wpdb, $pagenow, $ARMember, $arm_global_settings, $arm_subscription_plans;
			
			$default_rules = $this->arm_get_default_access_rules();
			$taxonomy_info = get_taxonomy($taxonomy);
				if (isset($default_rules[$taxonomy]) && !empty($default_rules[$taxonomy])) {
					$access_plans = trim(implode(',', $default_rules[$taxonomy]), ',');
                	update_metadata('arm_term',$term_id,'arm_protection','1');
					foreach( $default_rules[$taxonomy] as $key => $new_texonomy_plan ){
						/* Check if already term meta is exists */
						
						$check_exists_term_meta = $wpdb->get_results($wpdb->prepare("SELECT COUNT(*) as total FROM `".$ARMember->tbl_arm_termmeta."` WHERE arm_term_id = %d AND meta_key = %s AND meta_value = %d",$term_id,'arm_access_plan',$new_texonomy_plan));
						
						if( $check_exists_term_meta[0]->total > 0 ){
							/* Do Nothing */
						} else {
							$insert = $wpdb->query( $wpdb->prepare("INSERT INTO `".$ARMember->tbl_arm_termmeta."` (arm_term_id,meta_key,meta_value) VALUES (%d,%s,%d)",$term_id,'arm_access_plan',$new_texonomy_plan ));
						}
					}
				} else {
                update_metadata('arm_term',$term_id,'arm_protection','0');
                update_metadata('arm_term',$term_id,'arm_access_plan','0');
				}
			return;
		}
		/**
		 * Add `FIND_IN_SET` for search & compare value in post meta query.
		 */
		function arm_posts_where_find_in_set($where, $query)
		{
			global $wp, $wpdb, $current_user, $ARMember;
			foreach ($query->meta_query->queries as $index => $meta_query)
			{
				if (isset($meta_query['compare']) && 'arm_find_in_set' == strtolower($meta_query['compare'])) {
					$regex = "#\( ({$wpdb->postmeta}.meta_key = '" . preg_quote($meta_query['key']) . "') AND (CAST\({$wpdb->postmeta}.meta_value AS CHAR\)) = (\'" . preg_quote($meta_query['value']) . "') \)#";
					/**
					 * Replace the compare '=' with compare 'find_in_set'
					 */
					$where = preg_replace($regex, "($1 AND FIND_IN_SET($3,$2))", $where);
				}
				if (isset($meta_query['compare']) && 'arm_not_find_in_set' == strtolower($meta_query['compare']))
				{
					$regex = "#\( ({$wpdb->postmeta}.meta_key = '" . preg_quote($meta_query['key']) . "') AND (CAST\({$wpdb->postmeta}.meta_value AS CHAR\)) = (\'" . preg_quote($meta_query['value']) . "') \)#";
					/**
					 * Replace the compare '=' with compare 'find_in_set'
					 */
					$where = preg_replace($regex, "($1 AND NOT FIND_IN_SET($3,$2))", $where);
				}
			}
			return $where;
		}

		function arm_get_sub_categories($rule_records,$term_id,$planArr,$slug,$protection,$lvl = 0){
			$sub_cat_args = array(
				'hide_empty' => false,
				'orderby' => 'name',
				'order' => 'ASC',
				'parent' => $term_id,
				'taxonomy' => $slug,
				);
			$lvl ++;
			$space = "&#9866;";
			$spaces = str_repeat($space,$lvl);
			$sub_cat = get_categories($sub_cat_args);
			if( !empty($sub_cat) ){
				foreach( $sub_cat as $key => $category ){
					$protect = get_arm_term_meta($category->term_id, 'arm_protection', true);
					$protect = (!empty($protect)) ? $protect : 0;

					$item_plans = get_arm_term_meta($category->term_id, 'arm_access_plan');

					$item_plans = (!empty($item_plans)) ? $item_plans : array();
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
						$rule_records[$category->term_id] = array(
							'id' => $category->term_id,
							'title' => $spaces.'&nbsp;'.$category->name,
							'protection' => $protect,
							'plans' => $item_plans,
							);
						$sub_cat_args = array(
							'hide_empty' => false,
							'orderby' => 'name',
							'order' => 'ASC',
							'parent' => $term_id,
							'taxonomy' => $slug,
							);
						$sub_cat = get_categories($sub_cat_args);
						if( !empty($sub_cat) ){
							$rule_records = $this->arm_get_sub_categories($rule_records,$category->term_id,$planArr,$slug,$protection,$lvl);
						}
					}
				}
			}
			return $rule_records;
		}
        function arm_get_sub_nav_menu($rule_records, $term_id, $planArr, $slug, $protection, $lvl = 0) {
            global $arm_modal_view_in_menu;
            $menu = wp_get_nav_menu_object($slug);
            $menu_items = wp_get_nav_menu_items($menu->term_id);
            $children = $arm_modal_view_in_menu->get_nav_menu_item_children($term_id, $menu_items, false);
            if (count($children) > 0) {
                $lvl ++;
                    $space = "&#9866;";
                    $spaces = str_repeat($space, $lvl);
                foreach ($children as $key => $item) {
					
					$protect = 0;
                    $item_plans = get_post_meta($item->ID, 'arm_access_plan');
                    $item_plans = (!empty($item_plans)) ? $item_plans : array();
					
					if(count($item_plans) == 0)
						$protect = 0;
					else
						$protect = 1;
						
						
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
                        $rule_records[$item->ID] = array(
                            'id' => $item->ID,
                            'title' => $spaces . '&nbsp;' .$item->title,
                            'protection' => $protect,
                            'plans' => $item_plans,
                        );
                        $children_deep = $arm_modal_view_in_menu->get_nav_menu_item_children($item->ID, $menu_items, false);
                        if (count($children_deep) > 0) {
                            $rule_records = $this->arm_get_sub_nav_menu($rule_records, $item->ID, $planArr, $slug, $protection, $lvl);
                        }
                    }
                }
            }
            return $rule_records;
        }

    }
}

global $arm_access_rules;
$arm_access_rules = new ARM_access_rules();