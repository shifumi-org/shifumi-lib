<?php
if (!class_exists('ARM_common_hooks')) {
	class ARM_common_hooks
	{
		function __construct()
		{
			global $ARMember,$arm_crons,$arm_drip_rules,$arm_2checkout,$arm_authorize_net,$arm_paypal,$arm_stripe,$arm_stripe_sca,$arm_global_settings,$arm_member_forms,$arm_members_class,$arm_members_badges,$arm_membership_setup,$arm_modal_view_in_menu,$arm_restriction,$arm_shortcodes,$arm_social_feature,$is_woocommerce_feature,$arm_report_analytics,$arm_access_rules,$arm_pay_per_post_feature,$arm_transaction,$arm_email_settings, $arm_payment_gateways;
			
			
			/* Hide Update Notification */
			add_action('admin_init', array($ARMember, 'arm_hide_update_notice'), 1);
			add_action('admin_init', array($ARMember, 'arm_install_plugin_data'), 1000);
			add_action('admin_init',array($ARMember,'arm_redirect_to_update_page') );
			add_action('admin_init',array($arm_global_settings,'arm_plugin_add_suggested_privacy_content'),20);			
			add_action('admin_init', array($arm_shortcodes, 'arm_add_tinymce_styles'));
			
			add_action('admin_body_class', array($ARMember, 'arm_admin_body_class'));
			add_action('admin_menu', array($ARMember, 'arm_menu'), 27);
			add_action('admin_menu', array($ARMember, 'arm_set_last_menu'), 50);
			add_action('admin_bar_menu', array($ARMember, 'arm_add_debug_bar_menu'), 999);
			
			add_action('admin_enqueue_scripts', array($ARMember, 'set_css'), 11);
			add_action('admin_enqueue_scripts', array($ARMember, 'set_js'), 11);
			add_action('admin_enqueue_scripts', array($ARMember, 'set_global_javascript_variables'), 10);
			add_action('admin_enqueue_scripts', array($arm_global_settings, 'arm_add_page_label_css'), 20);
			add_action('admin_enqueue_scripts', array($is_woocommerce_feature, 'arm_enqueue_woocommerce_stylesheet'));
			
			add_action('admin_footer', array($ARMember, 'arm_add_document_video'), 1);
			add_action('admin_footer', array($ARMember, 'arm_add_new_version_release_note'), 1);
			add_action('admin_footer',array($arm_global_settings,'arm_rewrite_rules_for_profile_page'),100);
			add_action('admin_footer',array($arm_modal_view_in_menu,'arm_edit_nav_menu'),10);
			add_action('admin_footer', array($arm_report_analytics, 'arm_set_reports_submenu') );
			add_action('admin_footer', array($arm_shortcodes, 'arm_insert_shortcode_popup'));
			
			add_action('init', array($ARMember, 'arm_init_action'));
			add_action('init', array($ARMember, 'wpdbfix'));
			add_action('init', array($arm_crons, 'arm_add_crons'), 10);
			add_action('init', array($arm_global_settings, 'arm_apply_global_settings'), 200);
			add_action('init', array($arm_member_forms, 'arm_auto_lock_shared_account'));
			add_action('init', array($arm_modal_view_in_menu,'logout_from_menu_link'));
			
			add_action('init', array($arm_restriction, 'arm_set_current_user'), 11);
            add_action('init', array($arm_restriction, 'arm_restriction_init'), 12);

			
			/* Front end css and js */
			add_action('wp_head', array($ARMember, 'set_front_css'), 1);
			add_action('wp_head', array($ARMember, 'set_front_js'), 1);
			add_action('wp_head', array($ARMember, 'set_global_javascript_variables'));
			add_action('wp_head', array($arm_stripe_sca, 'arm_enqueue_stripe_js'),100);
			
			/* For Admin Menus. */
			add_action('adminmenu', array($ARMember, 'arm_set_adminmenu'));
			add_action('wp_logout', array($ARMember, 'ARM_EndSession'));
			add_action('wp_login', array($ARMember, 'ARM_EndSession'));
			
			
			add_action('wp_footer', array($ARMember, 'arm_set_js_css_conditionally'), 11);		
			add_action('wp_footer',array($arm_modal_view_in_menu,'arm_add_modal_popups_after_theme_loaded'));
			
			
			add_action('wp', array($arm_2checkout, 'arm_2checkout_ins_handle_response'), 5);
			add_action('wp', array($arm_authorize_net, 'arm_authorize_net_api_handle_response'), 5);
			add_action('wp', array($arm_paypal, 'arm_paypal_api_handle_response'), 5);
			add_action('wp', array($arm_stripe, 'arm_StripeEventListener'), 5);
			add_action('wp', array($arm_stripe_sca, 'arm_StripeEventListener'), 4);
			add_action('wp', array($arm_membership_setup, 'arm_membership_setup_preview_func'));
			add_action('wp', array($arm_restriction, 'arm_wp_head_redirect'), 6);
			add_action('wp', array($arm_social_feature, 'arm_twitter_login_callback'), 5);
			add_action('wp', array($arm_social_feature, 'arm_tumblr_login_callback'), 5);
            add_action('wp', array($arm_social_feature, 'arm_login_with_twitter'), 1);
            add_action('wp', array($arm_social_feature, 'arm_login_with_tumblr'), 1);
            add_action('wp', array($arm_social_feature, 'arm_login_with_linkedin'), 1);
			
			/* Member Iterations */
            add_action('user_register', array($arm_members_class, 'arm_user_register_hook_func'));
			add_action('user_register', array($arm_members_class, 'arm_add_capabilities_to_new_user'));
            add_action('profile_update', array($arm_members_class, 'arm_profile_update_hook_func'), 20, 2);
            add_action('delete_user', array($arm_members_class, 'arm_before_delete_user_action'), 10, 2);
            add_action('deleted_user', array($arm_members_class, 'arm_after_deleted_user_action'), 10, 2);
            add_action('set_user_role', array($arm_members_class,'arm_add_capabilities_to_change_user_role'), 10, 3);
			
			
			add_action('save_post', array($arm_access_rules, 'arm_save_post_rules'), 20, 3);
			add_action('save_post', array($arm_members_badges, 'arm_save_user_post_achieve'), 22, 3);
			add_action('delete_post', array($arm_members_badges, 'arm_delete_user_post_achieve'), 22);
			add_action('comment_post', array($arm_members_badges, 'arm_save_user_comment_achieve'), 10, 2);
			add_action('delete_comment', array($arm_members_badges, 'arm_delete_user_comment_achieve'), 10, 1);
            
			
			add_action('deleted_post', array($arm_drip_rules, 'arm_delete_post_drip_rules'), 20);

			/* for paid post feature */
			if( !empty($arm_pay_per_post_feature->isPayPerPostFeature) ){

				add_action( 'add_meta_boxes', array( $arm_pay_per_post_feature, 'arm_add_paid_post_metabox' ), 10, 2 );
				add_action( 'save_post', array( $arm_pay_per_post_feature, 'arm_save_paid_post_metabox'), 19, 3 );
				
				add_action( 'delete_post', array($arm_pay_per_post_feature, 'arm_move_to_trash_paid_post'), 22, 1);
                add_action( 'wp_trash_post', array( $arm_pay_per_post_feature, 'arm_move_to_trash_paid_post' ),22, 1 );
                add_action( 'untrash_post', array( $arm_pay_per_post_feature, 'arm_move_to_published_paid_post' ),22, 1  );

                add_action( 'admin_enqueue_scripts', array( $arm_pay_per_post_feature, 'arm_add_pay_per_post_script_data') );
                add_action( 'admin_init', array( $arm_pay_per_post_feature, 'arm_add_update_paid_post') );
                add_filter( 'the_content', array( $arm_pay_per_post_feature, 'arm_paid_post_content_check_restriction'), 10, 1 );
		add_filter( 'update_user_metadata', array( $arm_pay_per_post_feature, 'arm_update_user_paid_post_ids'), 10, 5);
				
				/* Rewrite Rules */
				//add_action('init', array($arm_pay_per_post_feature, 'armpay_per_post_add_fancy_url_rule'), 101);
			}
			/* for paid post feature */

			/*Element Templates Content Access Rules*/
			add_filter( 'elementor/frontend/builder_content_data', array( $arm_restriction, 'arm_elementor_templates_content_check_access_rules'), 10, 2);

			add_action('init', array($arm_transaction, 'arm_load_init_data'));

			add_action('init', array($arm_email_settings, 'arm_redirect_aweber_url'));

			add_action('admin_init', array( $arm_payment_gateways, 'arm_debug_log_download_file') );
		}
	}
}
global $arm_common_hooks;
$arm_common_hooks = new ARM_common_hooks();
