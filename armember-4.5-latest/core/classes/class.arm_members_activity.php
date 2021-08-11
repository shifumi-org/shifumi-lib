<?php
if (!class_exists('ARM_members_activity'))
{
	class ARM_members_activity
	{
		function __construct()
		{
			global $wpdb, $ARMember, $arm_slugs;
			
			add_action('arm_record_activity', array(&$this, 'arm_add_activity'), 1);
			add_action('wp_ajax_arm_delete_member_activities', array(&$this, 'arm_delete_member_activities'));
			/* Ajax Load More Activities */
			add_action('wp_ajax_nopriv_arm_crop_iamge', array(&$this, 'arm_crop_image'));
            add_action('wp_ajax_arm_crop_iamge', array(&$this, 'arm_crop_image'));


            add_action('wp_ajax_arm_upload_front', array(&$this, 'arm_upload_front'), 1);
	        add_action('wp_ajax_nopriv_arm_upload_front', array(&$this, 'arm_upload_front'), 1);

	        add_action('wp_ajax_arm_upload_cover', array(&$this, 'arm_upload_cover'), 1);
	        add_action('wp_ajax_nopriv_arm_upload_cover', array(&$this, 'arm_upload_cover'), 1);

	        add_action('wp_ajax_arm_upload_profile', array(&$this, 'arm_upload_profile'), 1);
	        add_action('wp_ajax_nopriv_arm_upload_profile', array(&$this, 'arm_upload_profile'), 1);

	        add_action('wp_ajax_arm_upload_badge', array(&$this, 'arm_upload_badge'), 1);

	        add_action('wp_ajax_arm_upload_social_icon', array(&$this, 'arm_upload_social_icon'), 1);

	        add_action('wp_ajax_arm_import_user', array(&$this, 'arm_import_user'), 1);
			
			add_action('wp_ajax_armactivatelicense', array(&$this, 'armreqact'));

			add_action('wp_ajax_armrenewlicense', array(&$this, 'arm_renew_license'));

			add_action('wp_ajax_armrenewuserbadge', array(&$this, 'arm_renew_user_badge'));
			
			add_action('wp_ajax_armdeactlic', array(&$this, 'armreqlicdeact'));
			global $check_sorting;
       		$check_sorting = "checksorting";
			
			global $check_version;
       		$check_version = "checkversion";
                
            add_action('admin_init', array(&$this, 'upgrade_data'));
		}
                
        function upgrade_data() {
			global $arm_newdbversion;
	
			if (!isset($arm_newdbversion) || $arm_newdbversion == "")
				$arm_newdbversion = get_option('arm_version');

			if( version_compare($arm_newdbversion,'2.0','<') ){
				update_option('arm_update_to_new_version',true);
			    update_option('arm_new_version','2.0');
			    $url = admin_url('admin.php?page=arm_update_page');
			    if( $_REQUEST['page'] != 'arm_update_page' ){
			        wp_redirect($url);
			        die();
			    }
			}
	
            if (version_compare($arm_newdbversion, '4.5', '<') && version_compare($arm_newdbversion,'1.8.1','>')) {
				$path = MEMBERSHIP_VIEWS_DIR . '/upgrade_latest_data.php';
				include($path);
			}
		}
		
		function arm_add_activity($activity = array())
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_social_feature;
			return false;
		}
		
		function armreqlicdeact() {
		
			$plugres = $this->armdeactivatelicense();
	
			if (isset($plugres) && $plugres != "") {
				echo $plugres;
				exit;
			} else {
				echo "Invalid Request";
				exit;
			}
			exit;
		}
		
		function checkversion($case = '') {
	
			$sortorder = get_option("armSortOrder");
			$sortid = get_option("armSortId");
			$issorted = get_option("armIsSorted");
			$isinfo = get_option("armSortInfo");
	
			if ($sortorder == "" || $sortid == "" || $issorted == "") {
				return 0;
			} else {
				$sortfield = $sortorder;
				$sortorderval = base64_decode($sortfield);
	
				$ordering = array();
				$ordering = explode("^", $sortorderval);
	
				$domain_name = str_replace('www.', '', $ordering[3]);
				$recordid = $ordering[4];
				$ipaddress = $ordering[5];
	
				$mysitename = get_bloginfo('name');
				$siteipaddr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
				$servername = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : '';
				$serverhost = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : '';
				$mysitedomain = str_replace('www.', '', $servername);
				$mysitedomain1 = str_replace('www.', '', $serverhost);
				$mysitedomain2 = str_replace('www.', '', $siteipaddr);
			
				if (($domain_name == $mysitedomain || $domain_name == $mysitedomain1 || $domain_name == $mysitedomain2) && ($recordid == $sortid)) {
					return 1;
				} else {
					return 0;
				}
				}
		}
		
		function arm_renew_user_badge()
		{
			global $wp_version;
			$lidata = "";
			$verifycode = get_option("armSortOrder");
			
			if($verifycode == "")
			{
				echo "Invalid Request Parameters";
				exit;
			}
			
			$urltopost = "https://www.reputeinfosystems.com/tf/plugins/armember/verify/update_arm_badge.php";
			$response = wp_remote_post($urltopost, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array('verifycode' => $verifycode),
				'user-agent' => 'ARM-WordPress/' . $wp_version . '; ' . ARM_HOME_URL,
				'cookies' => array()
					)
			);

			if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "")
				$responsemsg = $response["body"];
			else
				$responsemsg = "";

			if ($responsemsg != "") {
				$responsemsg = explode("|^|", $responsemsg);
				if (is_array($responsemsg) && count($responsemsg) > 0) {

					if (isset($responsemsg[0]) && $responsemsg[0] != "") {
						$msg = $responsemsg[0];
					} else {
						$msg = "";
					}
					
					if (isset($responsemsg[1]) && $responsemsg[1] != "") {
						$info = $responsemsg[1];
					} else {
						$info = "";
					}

					if ($msg == "1") {
						update_option("armSortOrder", $info);
						update_option("armIsBadgeUpdated", $info);
						delete_option('arm_badgeupdaterequired');
						echo "VERIFIED";
						exit;
					}
					else 
					{
						echo $msg;
						exit;
					}
				}
			}
			else 
			{
				echo "Invalid Request";
				exit;
			}			
		}
		
		
		function checksorting() {
			
			$sortorder = get_option("armSortOrder");
			$sortid = get_option("armSortId");
			$issorted = get_option("armIsSorted");
			$isinfo = get_option("armSortInfo");
	
			if ($sortorder == "" || $sortid == "" || $issorted == "") {
				return 0;
			} else {
				$sortfield = $sortorder;
				$sortorderval = base64_decode($sortfield);
	
				$ordering = array();
				$ordering = explode("^", $sortorderval);
				
				$domain_name = str_replace('www.', '', $ordering[3]);
				$recordid = $ordering[4];
				$ipaddress = $ordering[5];
	
				$mysitename = get_bloginfo('name');
				$siteipaddr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
				$servername = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : '';
				$serverhost = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : '';
				
				$mysitedomain = str_replace('www.', '', $servername);
				$mysitedomain1 = str_replace('www.', '', $serverhost);
				$mysitedomain2 = str_replace('www.', '', $siteipaddr);
				
				if (($domain_name == $mysitedomain || $domain_name == $mysitedomain1 || $domain_name == $mysitedomain2) && ($recordid == $sortid)) {
					return 1;
				}else {
					
					  $isoptionstored = "";
					  update_option('arm_is_social_feature', 0);
					  update_option('arm_is_social_login_feature', 0);
					  update_option('arm_is_drip_content_feature', 0);
				      update_option('arm_is_opt_ins_feature', 0);
				      update_option('arm_is_coupon_feature', 0);
				                update_option('arm_is_buddypress_feature', 0);
				                update_option('arm_is_woocommerce_feature', 0);
				                update_option('arm_is_multiple_membership_feature', 0);
				                update_option('arm_is_mycred_feature', 0);
						update_option('arm_is_invoice_tax_feature', 0);
						  
						  delete_option("armIsSorted");
						  delete_option("armSortOrder");
						  delete_option("armSortId");
						  delete_option("armSortInfo");
						  delete_option("armBadgeUpdated");
						  delete_option("armIsBadgeUpdated");
			  
						  delete_site_option("armIsSorted");
						  delete_site_option("armSortOrder");
						  delete_site_option("armSortId");
						  delete_site_option("armSortInfo");
						  delete_site_option("armBadgeUpdated");
						  delete_site_option("armIsBadgeUpdated");
						  
						  update_option('arm_isoptionstored', 1);
					return 0;
				}
			}
		}
		
		function arm_renew_license() {
			global $wp_version;
       
        $lidata = "";

        $lidata = $_POST["purchase_info"];

        $verifycode = get_option("armSortOrder");
        
        $valstring =  $lidata;
        $urltopost = "https://www.reputeinfosystems.com/tf/plugins/armember/verify/lic_renew_arm.php";

        $response = wp_remote_post($urltopost, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array('verifyrenew' => $valstring, 'verifycode' => $verifycode),
			'user-agent' => 'ARM-WordPress/' . $wp_version . '; ' . ARM_HOME_URL,
            'cookies' => array()
                )
        );

        if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "")
            $responsemsg = $response["body"];
        else
            $responsemsg = "";


        if ($responsemsg != "") {
            $responsemsg = explode("|^|", $responsemsg);
            if (is_array($responsemsg) && count($responsemsg) > 0) {

                if (isset($responsemsg[0]) && $responsemsg[0] != "") {
                    $msg = $responsemsg[0];
                } else {
                    $msg = "";
                }
                
                if (isset($responsemsg[1]) && $responsemsg[1] != "") {
                    $info = $responsemsg[1];
                } else {
                    $info = "";
                }

                if ($msg == "1") {
                    update_option("armSortInfo", $info);
                    echo "VERIFIED";
                    exit;
                }
                else 
                {
                	echo $msg;
                	exit;
            	}
            }
        } else {
            echo "Invalid Request";
            exit;
        }
    }


		function armdeactivatelicense() {
			global $wp_version;
			$siteinfo = array();
	
			$siteinfo[] = get_bloginfo('name');
			$siteinfo[] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
			$siteinfo[] = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : '';
			$siteinfo[] = MEMBERSHIP_URL;
			$siteinfo[] = get_option("arm_version");
	
			$newstr = implode("||", $siteinfo);
			$postval = base64_encode($newstr);
	
			$verifycode = get_option("armSortOrder");
	
			if (isset($verifycode) && $verifycode != "") {
				$urltopost = "https://www.reputeinfosystems.com/tf/plugins/armember/verify/lic_de_act.php";
	
	
				$response = wp_remote_post($urltopost, array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => array('verifypurchase' => $verifycode, 'postval' => $postval),
					'user-agent' => 'ARM-WordPress/' . $wp_version . '; ' . ARM_HOME_URL,
					'cookies' => array()
						)
				);
				
				if(is_wp_error($response)) 
				{
					$urltopost = "http://www.reputeinfosystems.com/tf/plugins/armember/verify/lic_de_act.php";
	
	
					$response = wp_remote_post($urltopost, array(
						'method' => 'POST',
						'timeout' => 45,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking' => true,
						'headers' => array(),
						'body' => array('verifypurchase' => $verifycode, 'postval' => $postval),
						'user-agent' => 'ARM-WordPress/' . $wp_version . '; ' . ARM_HOME_URL,
						'cookies' => array()
							)
					);
				}
				
				
	
				if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "")
					$responsemsg = $response["body"];
				else
					$responsemsg = "";
	
				$chkplugver = $this->chkplugversionth($responsemsg);
	
				return $chkplugver;
				exit;
			}
			else {
				$resp = "Invalid Request";
				return $resp;
				exit;
			}
		}
		function armreqact() {
			
			$plugres = $this->armverifypurchasecode();
	
			if (isset($plugres) && $plugres != "") {
				$responsetext = $plugres;
	
				if ($responsetext == "License Activated Successfully.") {
					echo "VERIFIED";
					exit;
				} else {
					echo $plugres;
					exit;
				}
			} else {
				echo "Invalid Request";
				exit;
			}
		}
	
		function chkplugversionth($myresponse) {
			if ($myresponse != "" && $myresponse == 1) {
				
				$new_key = '';
	
				$new_key = rand();
	
				$thresp = $this->checkthisvalidresp($new_key);
	
				if ($thresp == 1) {
					return "License Deactivted Sucessfully.";
					exit;
				} else {
					$resp = "Invalid Request";
					return $resp;
					exit;
				}
			} else {
				$resp = "Invalid Request";
				return $resp;
				exit;
			}
		}
		 function arm_get_remote_post_params($plugin_info = "") {
			global $wpdb;
	
			$action = "";
			$action = $plugin_info;
	
			if (!function_exists('get_plugins')) {
				require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}
			$plugin_list = get_plugins();
			$site_url = ARM_HOME_URL;
			$plugins = array();
	
			$active_plugins = get_option('active_plugins');
	
			foreach ($plugin_list as $key => $plugin) {
				$is_active = in_array($key, $active_plugins);
	
				//filter for only armember ones, may get some others if using our naming convention
				if (strpos(strtolower($plugin["Title"]), "armember") !== false) {
					$name = substr($key, 0, strpos($key, "/"));
					$plugins[] = array("name" => $name, "version" => $plugin["Version"], "is_active" => $is_active);
				}
			}
			$plugins = json_encode($plugins);
	
			//get theme info
			$theme = wp_get_theme();
			$theme_name = $theme->get("Name");
			$theme_uri = $theme->get("ThemeURI");
			$theme_version = $theme->get("Version");
			$theme_author = $theme->get("Author");
			$theme_author_uri = $theme->get("AuthorURI");
	
			$im = is_multisite();
			$sortorder = get_option("armSortOrder");
	
			$post = array("wp" => get_bloginfo("version"), "php" => phpversion(), "mysql" => $wpdb->db_version(), "plugins" => $plugins, "tn" => $theme_name, "tu" => $theme_uri, "tv" => $theme_version, "ta" => $theme_author, "tau" => $theme_author_uri, "im" => $im, "sortorder" => $sortorder);
	
			return $post;
		}
	
		
		function armgetapiurl() {
			$api_url = 'https://arpluginshop.com/';
			return $api_url;
		}
		
		function checkthisvalidresp($new_key) {
			if ($new_key != "") {
				delete_option("armIsSorted");
				delete_option("armSortOrder");
				delete_option("armSortId");
				delete_option("armSortInfo");
	
				delete_site_option("armIsSorted");
				delete_site_option("armSortOrder");
				delete_site_option("armSortId");
				delete_site_option("armSortInfo");
				
				update_option('arm_is_user_private_content_feature', 0);
				update_option('arm_is_social_feature', 0);
				update_option('arm_is_social_login_feature', 0);
				update_option('arm_is_drip_content_feature', 0);
				update_option('arm_is_opt_ins_feature', 0);
				update_option('arm_is_coupon_feature', 0);
				update_option('arm_is_buddypress_feature', 0);
                update_option('arm_is_woocommerce_feature', 0);
                update_option('arm_is_multiple_membership_feature', 0);
                update_option('arm_is_mycred_feature', 0);
                update_option('arm_is_invoice_tax_feature', 0);
					
	
				return "1";
				exit;
			} else {
				$resp = "Invalid Request";
				return $resp;
				exit;
			}
		}
		   function getwpversion() {

			global $arm_version;
			$bloginformation = array();
			$str = $this->get_rand_alphanumeric(10);
	
			if (is_multisite())
				$multisiteenv = "Multi Site";
			else
				$multisiteenv = "Single Site";
	
			$bloginformation[] = get_bloginfo('name');
			$bloginformation[] = get_bloginfo('description');
			$bloginformation[] = ARM_HOME_URL;
			$bloginformation[] = '';
			$bloginformation[] = get_bloginfo('version');
			$bloginformation[] = get_bloginfo('language');
			$bloginformation[] = $arm_version;
			$bloginformation[] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
			$bloginformation[] = $str;
			$bloginformation[] = $multisiteenv;
	
			$this->checksite($str);
	
			$valstring = implode("||", $bloginformation);
			$encodedval = base64_encode($valstring);
	
			$urltopost = "https://reputeinfosystems.net/armember/wp_in.php";
			$response = wp_remote_post($urltopost, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array('wpversion' => $encodedval),
				'cookies' => array()
					)
			);
		}

		  function get_rand_alphanumeric($length) {
			  
			  if ($length > 0) {
				  $rand_id = "";
				  for ($i = 1; $i <= $length; $i++) {
					  mt_srand((double) microtime() * 1000000);
					  $num = mt_rand(1, 36);
					  $rand_id .= $this->assign_rand_value($num);
				  }
			  }
			  return $rand_id;
		  }
	  	 
		 
		 function armverifypurchasecode() {
       global $wp_version;
        $lidata = array();

        $lidata[] = $_POST["cust_name"];
        $lidata[] = $_POST["cust_email"];
        $lidata[] = $_POST["license_key"];
        $lidata[] = $_POST["domain_name"];
		
		

        $pluginuniquecode = $this->generateplugincode();
        $lidata[] = $pluginuniquecode;
        $lidata[] = MEMBERSHIP_URL;
        $lidata[] = get_option("arm_version");
		$lidata[] = $_POST["is_receive_updates"];

        $valstring = implode("||", $lidata);
        $encodedval = base64_encode($valstring);

        $urltopost = "https://www.reputeinfosystems.com/tf/plugins/armember/verify/lic_act_arm.php";

        $response = wp_remote_post($urltopost, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array('verifypurchase' => $encodedval),
			'user-agent' => 'ARM-WordPress/' . $wp_version . '; ' . ARM_HOME_URL,
            'cookies' => array()
                )
        );
		
		if(is_wp_error($response)) 
		{
			$urltopost = "http://www.reputeinfosystems.com/tf/plugins/armember/verify/lic_act_arm.php";

			$response = wp_remote_post($urltopost, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array('verifypurchase' => $encodedval),
				'user-agent' => 'ARM-WordPress/' . $wp_version . '; ' . ARM_HOME_URL,
				'cookies' => array()
					)
			);
		}

        if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "")
            $responsemsg = $response["body"];
        else
            $responsemsg = "";


        if ($responsemsg != "") {
            $responsemsg = explode("|^|", $responsemsg);
            if (is_array($responsemsg) && count($responsemsg) > 0) {

                if (isset($responsemsg[0]) && $responsemsg[0] != "") {
                    $msg = $responsemsg[0];
                } else {
                    $msg = "";
                }
                if (isset($responsemsg[1]) && $responsemsg[1] != "") {
                    $code = $responsemsg[1];
                } else {
                    $code = "";
                }
                if (isset($responsemsg[2]) && $responsemsg[2] != "") {
                    $info = $responsemsg[2];
                } else {
                    $info = "";
                }

                if ($msg == "1") {
                    $checklic = $this->checksoringcode($code, $info);

                    if ($checklic == "1") {
                        return "License Activated Successfully.";
                        exit;
                    } else {
                        return "Invalid Request";
                        exit;
                    }
                } else if ($msg == "THIS PURCHASED CODE IS ALREADY USED FOR ANOTHER DOMAIN") {

                    return $responsemsg[0] . '||' . $responsemsg[1];
                    exit;
                } else {
                    return $responsemsg[0];
                    exit;
                }
            } else {
                return $responsemsg;
                exit;
            }
        } else {
            return "Invalid Request";
            exit;
        }
    }
	
	function checksoringcode($code, $info) {
        

        $mysortid = base64_decode($code);
        $mysortid = explode("^", $mysortid);

        if ($mysortid != "" && count($mysortid) > 0) {
            $setdata = $this->setdata($code, $info);

            return $setdata;
            exit;
        } else {
            return 0;
            exit;
        }
    }
	
	 function setdata($code, $info) {
        if ($code != "") {
            $mysortid = base64_decode($code);
            $mysortid = explode("^", $mysortid);
            $mysortid = $mysortid[4];

            update_option("armIsSorted", "Yes");
            update_option("armSortOrder", $code);
            update_option("armSortId", $mysortid);
            update_option("armSortInfo", $info);

            return 1;
            exit;
        } else {
            return 0;
            exit;
        }
    }
	
	function generateplugincode() {
        $siteinfo = array();

        $siteinfo[] = get_bloginfo('name');
        $siteinfo[] = get_bloginfo('description');
        $siteinfo[] = ARM_HOME_URL;
        $siteinfo[] = get_bloginfo('admin_email');
        $siteinfo[] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';

        $newstr = implode("^", $siteinfo);
        $postval = base64_encode($newstr);

        return $postval;
    }
		  function assign_rand_value($num) {
	  
			  switch ($num) {
				  case "1" : $rand_value = "a";
					  break;
				  case "2" : $rand_value = "b";
					  break;
				  case "3" : $rand_value = "c";
					  break;
				  case "4" : $rand_value = "d";
					  break;
				  case "5" : $rand_value = "e";
					  break;
				  case "6" : $rand_value = "f";
					  break;
				  case "7" : $rand_value = "g";
					  break;
				  case "8" : $rand_value = "h";
					  break;
				  case "9" : $rand_value = "i";
					  break;
				  case "10" : $rand_value = "j";
					  break;
				  case "11" : $rand_value = "k";
					  break;
				  case "12" : $rand_value = "l";
					  break;
				  case "13" : $rand_value = "m";
					  break;
				  case "14" : $rand_value = "n";
					  break;
				  case "15" : $rand_value = "o";
					  break;
				  case "16" : $rand_value = "p";
					  break;
				  case "17" : $rand_value = "q";
					  break;
				  case "18" : $rand_value = "r";
					  break;
				  case "19" : $rand_value = "s";
					  break;
				  case "20" : $rand_value = "t";
					  break;
				  case "21" : $rand_value = "u";
					  break;
				  case "22" : $rand_value = "v";
					  break;
				  case "23" : $rand_value = "w";
					  break;
				  case "24" : $rand_value = "x";
					  break;
				  case "25" : $rand_value = "y";
					  break;
				  case "26" : $rand_value = "z";
					  break;
				  case "27" : $rand_value = "0";
					  break;
				  case "28" : $rand_value = "1";
					  break;
				  case "29" : $rand_value = "2";
					  break;
				  case "30" : $rand_value = "3";
					  break;
				  case "31" : $rand_value = "4";
					  break;
				  case "32" : $rand_value = "5";
					  break;
				  case "33" : $rand_value = "6";
					  break;
				  case "34" : $rand_value = "7";
					  break;
				  case "35" : $rand_value = "8";
					  break;
				  case "36" : $rand_value = "9";
					  break;
			  }
			  return $rand_value;
		  }
	  
		  function checksite($str) {
			  update_option('arm_wp_get_version', $str);
		  }
		
		
		function arm_delete_member_activities()
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
			$delete_act = $wpdb->query("DELETE FROM `".$ARMember->tbl_arm_activity."` WHERE `arm_type`!='membership'");
			if ($delete_act) {
				$response = array('type' => 'success', 'msg' => __('Member activities has been deleted successfully.', 'ARMember'));
			} else {
				$response = array('type' => 'error', 'msg' => __('There is a error while deleting member activities, please try again.', 'ARMember'));
			}
			echo json_encode($response);
			die();
		}
		function arm_get_activity_by($field = '', $value = '', $limit = '', $object_type = ARRAY_A)
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans;
			$object_type = !empty($object_type) ? $object_type : ARRAY_A;
			$limit = (!empty($limit)) ? " LIMIT " . $limit : "";
			$result = false;
			if (!empty($field) && $value != '') {
				$result = $wpdb->get_results("SELECT * FROM `".$ARMember->tbl_arm_activity."` WHERE `$field`='$value' ORDER BY `arm_activity_id` DESC $limit", $object_type);
			}
			return $result;
		}
        function arm_crop_image() {

            $_POST['update_meta'] = isset($_POST['update_meta']) ? $_POST['update_meta'] : '';

            $user_id = get_current_user_id();


            /*this change need to confirm with multisite*/
            $_POST['src'] = MEMBERSHIP_UPLOAD_URL.'/'.basename($_POST['src']);

            $info = getimagesize(MEMBERSHIP_UPLOAD_DIR . '/' . basename($_POST['src']));
            $file = $_POST['src'];
        	$file1 = MEMBERSHIP_UPLOAD_DIR . '/' . basename($_POST['src']);
            $orgnl_hw = getimagesize($file1);
            $orgnl_w = $orgnl_hw[0];
            $orgnl_h = $orgnl_hw[1];
            $targ_x1 = 0;
            $targ_y1 = 0;
            $targ_x2 = $orgnl_w;
            $targ_y2 = $orgnl_h;
            $is_crop = false;
            if(isset($_POST['cord'])) {
            	$crop = explode(',', $_POST['cord']);
            	if ($crop[2] != 0 && $crop[3] != 0) {
		            $targ_x1 = $crop[0];
		            $targ_y1 = $crop[1];
		            $targ_x2 = $crop[2];
		            $targ_y2 = $crop[3];
            		$is_crop = true;
            	}
            }

            if ($_POST['type'] == 'profile') {

                if ($_POST['update_meta'] != 'no') {
                    update_user_meta($user_id, 'avatar', $file);
                    do_action('arm_upload_bp_avatar', $user_id);
                }

                $thumb_w = 220;
                $thumb_h = 220;
            } else if ($_POST['type'] == 'cover') {
                $thumb_w = 918;
                $thumb_h = 320;

                if ($_POST['update_meta'] != 'no') {
                    update_user_meta($user_id, 'profile_cover', $file);
                    do_action('arm_upload_bp_profile_cover', $user_id);
                }
            }
            if ($_POST['rotate'] != 'undefined') {
	            $rotation = $_POST['rotate'];
	            if ($rotation == -90 || $rotation == 270) {
	                $rotation = 90;
	            } elseif ($rotation == -180 || $rotation == 180) {
	                $rotation = 180;
	            } elseif ($rotation == -270 || $rotation == 90) {
	                $rotation = 270;
	            }
            	$new_targ_x1 = $targ_x1;
            	$new_targ_y1 = $targ_y1;
	            $fileTemp = MEMBERSHIP_UPLOAD_DIR . '/' . basename($_POST['src']);
	            $image_info = getimagesize($fileTemp);
				$original_width = $image_info[0];
				$original_height = $image_info[1];
				$new_width = abs($targ_x2 - $new_targ_x1);
				$new_height = abs($targ_y2 - $new_targ_y1);
	            if ($info['mime'] == 'image/png') {
	            	$source = imagecreatefrompng($fileTemp);
		            $imageRotate = imagerotate($source, $rotation, 0);
		            $rotated_width = imagesx($imageRotate);
					$rotated_height = imagesy($imageRotate);
					$dx = $rotated_width - $original_width;
					$dy = $rotated_height - $original_height;
					$crop_x = 0;
					$crop_y = 0;
					if($is_crop) {
						$crop_x = $dx/2 + $new_targ_x1;
						$crop_y = $dy/2 + $new_targ_y1;
					}
					$new_image = imagecreatetruecolor($targ_x2, $targ_y2);
	            	if($is_crop) {
		            	imagealphablending($new_image, false);
	                	imagesavealpha($new_image, true);
						imagecopyresampled($new_image, $imageRotate, 0, 0, $targ_x1, $targ_y1, $targ_x2, $targ_y2, $targ_x2, $targ_y2);
	            		$upload = imagepng($new_image, $fileTemp);
					} else {
	            		$upload = imagepng($imageRotate, $fileTemp);
					}
	            	$file = MEMBERSHIP_UPLOAD_DIR . '/' . basename($_POST['src']);
	            	$original_info = getimagesize($file);
	                $original_w = $original_info[0];
	                $original_h = $original_info[1];
	                $original_img = imagecreatefrompng($file);
	                $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);
	                imagealphablending($thumb_img, false);
	                imagesavealpha($thumb_img, true);
	                imagecopyresampled($thumb_img, $original_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
	                imagepng($thumb_img, MEMBERSHIP_UPLOAD_DIR . '/' . basename($file));
	            } else {
	            	$source = imagecreatefromjpeg($fileTemp);
		            $imageRotate = imagerotate($source, $rotation, 0);
		            $rotated_width = imagesx($imageRotate);
					$rotated_height = imagesy($imageRotate);
					$dx = $rotated_width - $original_width;
					$dy = $rotated_height - $original_height;
					$crop_x = 0;
					$crop_y = 0;
					$targ_x1 = $new_targ_x1;
					if($is_crop) {
						$crop_x = $dx/2 + $new_targ_x1;
						$crop_y = $dy/2 + $new_targ_y1;
					}
					$new_image = imagecreatetruecolor($targ_x2, $targ_y2);
	            	if($is_crop) {
						imagecopyresampled($new_image, $imageRotate, 0, 0, $targ_x1, $targ_y1, $targ_x2, $targ_y2, $targ_x2, $targ_y2);
	            		$upload = imagejpeg($new_image, $fileTemp);
					} else {
	            		$upload = imagejpeg($imageRotate, $fileTemp);
					}
	            	$file = MEMBERSHIP_UPLOAD_DIR . '/' . basename($_POST['src']);
	            	$original_info = getimagesize($file);
	                $original_w = $original_info[0];
	                $original_h = $original_info[1];
	                $original_img = imagecreatefromjpeg($file);
	                $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);
	                imagecopyresampled($thumb_img, $original_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
	                imagejpeg($thumb_img, MEMBERSHIP_UPLOAD_DIR . '/' . basename($file));
	            }
            }

            if ($_POST['type'] == 'profile') {
                if ($_POST['update_meta'] != 'no') {
                    update_user_meta($user_id, 'avatar', $_POST['src']);
                    do_action('arm_after_upload_bp_avatar', $user_id);
                }
            } else if ($_POST['type'] == 'cover') {      
                if ($_POST['update_meta'] != 'no') {
                    update_user_meta($user_id, 'profile_cover', $_POST['src']);
                    do_action('arm_after_upload_bp_profile_cover', $user_id);
                }
            }
            
            echo $_POST['src'];
            die();
        }

        function path_only($file) {
            return trailingslashit(dirname($file));
        }

        function arm_allowed_wp_mime_types()
        {
        	$mimes = get_allowed_mime_types();
	        ksort($mimes);
	        $mcount = count($mimes);
	        $third = ceil($mcount / 3);
	        $c = 0;
	        $mimes['exe'] = '';
	        unset($mimes['exe']);

	        $allowed_mimes = array();

	        foreach( $mimes as $ext => $type ){
	            if( strpos($ext, '|') !== false ){
	                $exts = explode('|',$ext);
	                foreach( $exts as $extension){
	                    if( $extension != '' ){
	                        array_push($allowed_mimes,$extension);
	                    }
	                }
	            } else {
	                array_push($allowed_mimes,$ext);
	            }
	        }

	        return $allowed_mimes;
        }

        function arm_upload_front() {
	        $upload_dir = MEMBERSHIP_UPLOAD_DIR.'/';
	        $upload_url = MEMBERSHIP_UPLOAD_URL.'/';

	        $file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	        $response = "";
	        if ($file_name)
	        {
	        	$content_length = (int) $_SERVER['CONTENT_LENGTH'];
	        	$file_size_new = number_format( ($content_length/1048576), 2, '.', '');

	        	$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
	            	$arm_upload_file_path = $upload_dir.$file_name;
	            	$file_result = $this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);

	                if(isset($_REQUEST['arm_file_type']) && $_REQUEST['arm_file_type'] == 'arm_stripe_icon' ) {
	                	if(file_exists($upload_dir . $file_name)) {
		                	$image = getimagesize($upload_dir . $file_name);
							$width = isset($image[0]) ? $image[0] : 0;
							$height = isset($image[1]) ? $image[1] : 0;
							if($width != 70 || $height != 70) {
								unlink($upload_dir . $file_name);
								$response['status'] = "error";
								$response['message'] = esc_html__("Select Logo image with 70X70 px", "ARMember");
								echo json_encode($response);
								die;
							}
		                }
	                }
	                $response = $upload_url . $file_name;
	            }
	            echo $response;
	            exit;
	        } else {
	            $files = $_FILES['armfileselect'];
	            $file_size = (isset($_REQUEST['allow_size'])) ? $_REQUEST['allow_size'] : '';
	            $file_name = $_REQUEST['fname'];
	            $file_size_new = $files['size'];
	            $file_size_new = number_format($file_size_new / 1048576, 2, '.', '');
	            $arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $files);
	        	if($arm_is_valid_file)
	        	{
	                if (!empty($file_size) && ($file_size_new > $file_size)) {
	                    $response = "<p class='error_upload_size'>".__('File size not allowed', 'ARMember')."</p>";
	                } else {	                		
	                	$arm_upload_file_path = $upload_dir . $file_name;
	                	$this->arm_upload_file_function($files['tmp_name'], $arm_upload_file_path);
	                    $response = $upload_url . $file_name;
	                    echo "<p class='uploaded'>" . $upload_url . $file_name . "</p>";
	                }
	            }
	        }
	        exit;
	    }

	    function arm_upload_cover() {
	        $upload_dir = MEMBERSHIP_UPLOAD_DIR.'/';
	        $upload_url = MEMBERSHIP_UPLOAD_URL.'/';

	        $file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	        $response = "";
	        $userID = get_current_user_id();
	        if ($file_name && !empty($userID) && $userID != 0) {

	        	$content_length = (int) $_SERVER['CONTENT_LENGTH'];
	        	$file_size_new = number_format( ($content_length/1048576), 2, '.', '');

	        	$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
	            	//$oldCover = get_user_meta($userID, 'profile_cover', true);

					$arm_upload_file_path = $upload_dir.$file_name;
	                $this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);

	                $response = $upload_url . $file_name;
	                echo $response;
	                exit;
	            }
	        } else {
	            $files = $_FILES['armfileselect'];
	            $file_size = (isset($_REQUEST['allow_size'])) ? $_REQUEST['allow_size'] : '';
	            $file_name = $_REQUEST['fname'];
	            $file_size_new = $_FILES['armfileselect']['size'];
	            $file_size_new = number_format($file_size_new / 1048576, 2, '.', '');

	            $arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $files );
	        	if($arm_is_valid_file)
	        	{
	                if (!empty($file_size) && ($file_size_new > $file_size)) {
	                    $response = "<p class='error_upload_size'>" . __('File size not allowed', 'ARMember') . "</p>";
	                } else {
	                	$arm_upload_file_path = $upload_dir . $file_name;
	                	$this->arm_upload_file_function($files['tmp_name'], $arm_upload_file_path);
	                    $response = $upload_url . $file_name;
	                    echo "<p class='uploaded'>" . $upload_url . $file_name . "</p>";
	                }
	            }
	        }
	        exit;
	    }

	    function arm_upload_profile() {
	        $upload_dir = MEMBERSHIP_UPLOAD_DIR.'/';
	        $upload_url = MEMBERSHIP_UPLOAD_URL.'/';

	        $file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	        $response = "";
	        $userID = get_current_user_id();
	        if ($file_name && !empty($userID) && $userID != 0) {
	            //$oldCover = get_user_meta($userID, 'profile_cover', true);
	            $content_length = (int) $_SERVER['CONTENT_LENGTH'];
	    	    $file_size_new = number_format( ($content_length/1048576), 2, '.', '');

	            $arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
					$arm_upload_file_path = $upload_dir.$file_name;
	                $this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);

	                $response = $upload_url . $file_name;
	                echo $response;
	                exit;
	            }
	        } else {
	            $files = $_FILES['armfileselect'];
	            $file_size = (isset($_REQUEST['allow_size'])) ? $_REQUEST['allow_size'] : '';
	            $file_name = $_REQUEST['fname'];
	            $file_size_new = $files['size'];
	    	    $file_size_new = number_format($file_size_new / 1048576, 2, '.', '');
	            
	            $arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $files );
	        	if($arm_is_valid_file)
	        	{
	                if (!empty($file_size) && ($file_size_new > $file_size)) {
	                    $response = "<p class='error_upload_size'>" . __('File size not allowed', 'ARMember') . "</p>";
	                } else {
	                	$arm_upload_file_path = $upload_dir . $file_name;
	                	$this->arm_upload_file_function($files['tmp_name'], $arm_upload_file_path);
	                    $response = $upload_url . $file_name;
	                    echo "<p class='uploaded'>" . $upload_url . $file_name . "</p>";
	                }
	            }
	        }
	        exit;
	   }

	   function arm_upload_badge()
	   {
	   		global $arm_capabilities_global;
	   		if(current_user_can($arm_capabilities_global['arm_badges']))
	   		{
		   		$upload_dir = MEMBERSHIP_UPLOAD_DIR.'/social_badges/';
		        $upload_url = MEMBERSHIP_UPLOAD_URL.'/social_badges/';

		        if (!is_dir($upload_dir)) {
					wp_mkdir_p($upload_dir);
				}

				$file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
				$response = "";
				if ($file_name)
				{
					$content_length = (int) $_SERVER['CONTENT_LENGTH'];
					$file_size_new = number_format( ($content_length/1048576), 2, '.', '');

					$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
		        	if($arm_is_valid_file)
		        	{
						$arm_upload_file_path = $upload_dir.$file_name;
		            	$this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);
						$response = $file_name;
					}
					echo $response;
					exit;
				} else {
					$files = $_FILES['armfileselect'];
					$file_size = (isset($_REQUEST['allow_size'])) ? $_REQUEST['allow_size'] : '';
					$file_name = $_REQUEST['fname'];
					$file_size_new = $files['size'];
					$file_size_new = number_format($file_size_new / 1048576, 2, '.', '');

					$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $files );
		        	if($arm_is_valid_file)
		        	{
						if (!empty($file_size) && ($file_size_new > $file_size)) {
							$response = "<p class='error_upload_size'>".__('File size not allowed', 'ARMember')."</p>";
						} else {
		                	$arm_upload_file_path = $upload_dir . $file_name;
		                	$this->arm_upload_file_function($files['tmp_name'], $arm_upload_file_path);
							$response = $upload_url . $file_name;
							echo "<p class='uploaded'>" . $file_name . "</p>";
						}
					}
				}
			}
			exit;
	   }

	   function arm_upload_social_icon()
	   {
	   		$upload_dir = MEMBERSHIP_UPLOAD_DIR.'/social_icon/';
	        $upload_url = MEMBERSHIP_UPLOAD_URL.'/social_icon/';

	        if (!is_dir($upload_dir)) {
				wp_mkdir_p($upload_dir);
			}

			$file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
			$response = "";
			if ($file_name)
			{
				$content_length = (int) $_SERVER['CONTENT_LENGTH'];
				$file_size_new = number_format( ($content_length/1048576), 2, '.', '');

				$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
	            	$arm_upload_file_path = $upload_dir.$file_name;
	            	$this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);
					$response = $upload_url.$file_name;
				}
				echo $response;
				exit;
			} else {
				$files = $_FILES['armfileselect'];
				$file_size = (isset($_REQUEST['allow_size'])) ? $_REQUEST['allow_size'] : '';
				$file_name = $_REQUEST['fname'];
				$file_type_new = $_FILES['armfileselect']['type'];
				$file_size_new = $_FILES['armfileselect']['size'];
				$file_size_new = number_format($file_size_new / 1048576, 2, '.', '');

				$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
					if (!empty($file_size) && ($file_size_new > $file_size)) {
						$response = "<p class='error_upload_size'>".__('File size not allowed', 'ARMember')."</p>";
					} else {
	                	$arm_upload_file_path = $upload_dir.$file_name;
	                	$this->arm_upload_file_function($files['tmp_name'], $arm_upload_file_path);
						$response = $upload_url . $file_name;
						echo "<p class='uploaded'>" . $upload_url.$file_name . "</p>";
					}
				}
			}

	   }

        function arm_import_user() 
	    {
	    	global $ARMember, $arm_capabilities_global;
	    	
	    	$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');

	        $upload_dir = MEMBERSHIP_UPLOAD_DIR.'/';
	        $upload_url = MEMBERSHIP_UPLOAD_URL.'/';
	        
	        $file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	        $response = "";
	        $userID = get_current_user_id();
	        if ($file_name && !empty($userID) && $userID != 0) {
	        	$file_size_new = $_FILES['armfileselect']['size'];
	        	$file_size_new = number_format($file_size_new / 1048576, 2, '.', '');

	            $arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
		    		$arm_upload_file_path = $upload_dir.$file_name;
	            	$this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);
	                $response = $upload_url . $file_name;
	                echo $response;
	                exit;
	            }
	        }
	        echo $response;
	        exit;
	    }

	    function arm_upload_file_function($source, $destination){
            if( empty( $source ) || empty( $destination ) ){
                return false;
            }

            if( !function_exists('WP_Filesystem' ) ){
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }

            WP_Filesystem();
            global $wp_filesystem;
            
            $file_content = $wp_filesystem->get_contents( $source );

            $result = $wp_filesystem->put_contents( $destination, $file_content, 0777 );

            return $result;
        }

	    function arm_check_for_invalid_data( $file_content = '' ){
	    	if( '' == $file_content ){
	    		return true;
	    	}

	    	$arm_valid_pattern = '/(\<\?(php))/';

	    	if( preg_match($arm_valid_pattern,$file_content) ){
	            return false;
	        }

	        return true;
	    }

	    function arm_check_valid_file_ext_data($file_name, $file_size, $arm_files_arr)
        {
        	$is_valid_file = 0;
        	if ($file_name && $file_size <= 20 )
	        {
	        	$arm_allowed_mimes = $this->arm_allowed_wp_mime_types();
        		$denyExts = array("php", "php3", "php4", "php5", "pl", "py", "jsp", "asp", "exe", "cgi");

	        	$checkext = explode(".", $file_name);
	            $ext = strtolower( $checkext[count($checkext) - 1] );
	            
	            $actual_file_name = $arm_files_arr['name'];
	            $actual_checkext = explode(".", $actual_file_name);
	            $actual_ext = strtolower( $actual_checkext[count($actual_checkext) - 1] );

	            if (!in_array($ext, $denyExts) && in_array($ext,$arm_allowed_mimes) && !in_array($actual_ext, $denyExts) && in_array($actual_ext,$arm_allowed_mimes)) 
	            {
	            	if( !function_exists('WP_Filesystem' ) )
	            	{
		                require_once(ABSPATH . 'wp-admin/includes/file.php');
		            }
	            	WP_Filesystem();
		            global $wp_filesystem;
		            $file_content = $wp_filesystem->get_contents($arm_files_arr['tmp_name']);

            		$valid_data = $this->arm_check_for_invalid_data( $file_content );

            		if( ! $valid_data ){
            			echo "<p class='error_upload_size'>" . esc_html__('The file could not be uploaded due to security reason as it contains malicious code', 'ARMember'). "</p>";
            			header('HTTP/1.0 401 Unauthorized');
        				die;
            		}
            		else {
            			$is_valid_file = 1;
            		}
	            }
	        }
	        else {
	        	echo "<p class='error_upload_size'>" . esc_html__('This file could not be processed due file limit exceeded.', 'ARMember'). "</p>";
        		die;
	        }
	        return $is_valid_file;
        }

    }
}
global $arm_members_activity;
$arm_members_activity = new ARM_members_activity();