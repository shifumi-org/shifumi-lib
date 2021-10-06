<?php

if (!class_exists('ARM_pay_per_post_feature')) {

    class ARM_pay_per_post_feature {
    	
        var $paid_post_settings;
        
        var $isPayPerPostFeature;

        function __construct() {
        	
            global $wpdb, $ARMember, $arm_slugs;
        	
            $is_paid_post_feature = get_option('arm_is_pay_per_post_feature', 0);

        	$this->isPayPerPostFeature = ($is_paid_post_feature == '1') ? true : false;

        	add_action( 'wp_ajax_arm_install_free_plugin', array( $this, 'arm_install_free_plugin' ) );
        
        	add_action( 'wp_ajax_arm_install_plugin', array( $this, 'arm_plugin_install' ), 10 );
        
        	add_action( 'wp_ajax_arm_active_plugin', array( $this, 'arm_activate_plugin' ), 10 );
        
        	add_action( 'wp_ajax_arm_deactive_plugin', array( $this, 'arm_deactivate_plugin' ), 10 );
        	
            if( $this->isPayPerPostFeature == true ){

                add_action( 'arm_shortcode_add_other_tab_buttons',array( $this,'arm_paid_post_shortcode_add_tab_buttons' ) );

                add_action( 'wp_ajax_get_paid_post_data', array( $this, 'arm_retrieve_paid_post_data' ) );

                add_action( 'wp_ajax_arm_get_paid_post_members_data', array( $this, 'arm_get_paid_post_members_data_func' ) );

                add_action( 'wp_ajax_arm_get_paid_post_item_options', array( $this, 'arm_get_paid_post_item_options' ) );

                add_action( 'wp_ajax_arm_update_paid_post_status', array( $this, 'arm_update_paid_post_status' ) );

                add_action( 'wp_ajax_arm_delete_single_paid_post', array( $this, 'arm_delete_single_paid_post' ) );

                add_action( 'wp_ajax_arm_edit_paid_post_data', array( $this, 'arm_edit_paid_post_data' ) );

                add_filter( 'arm_display_shortcode_buttons_on_tinymce', array( $this, 'arm_display_shortcode_buttons_for_alternate_button' ), 10, 2 );

                add_filter( 'arm_allowed_pages_for_media_buttons', array( $this, 'arm_allowed_pages_for_media_buttons_buttons' ), 10, 2 );

                add_filter( 'arm_allowed_post_type_for_external_editors', array( $this, 'arm_allowed_post_type_for_external_editors_callback' ), 10, 2 );

                add_filter( 'arm_allowed_pages_for_shortcode_popup', array( $this, 'arm_allowed_pages_for_shortcode_popup_callback' ), 10 );
                
                add_filter( 'arm_enqueue_shortcode_styles', array( $this, 'arm_enqueue_shortcode_styles_callback' ), 10 );

                add_filter( 'arm_modify_restriction_plans_outside', array( $this, 'arm_add_paid_post_plan_for_restriction' ), 10, 2 );

                add_action( 'wp_ajax_arm_add_paid_post_data', array( $this, 'arm_add_paid_post_data_callback' ) );

                add_action( 'wp_ajax_arm_update_paid_post_data', array( $this, 'arm_update_paid_post_data_callback') );

                add_filter( 'arm_setup_data_before_setup_shortcode', array( $this, 'arm_modify_setup_data_for_paid_post_type_setup'), 10, 2 );

                add_filter( 'arm_all_active_subscription_plans', array( $this, 'arm_add_paid_post_plan_in_active_subscription_pans'), 10 );

                add_filter( 'arm_after_setup_plan_section', array( $this, 'arm_add_paid_post_plan_id'), 10, 3 );
		
        		add_shortcode( 'arm_paid_post_buy_now', array( $this, 'arm_paid_post_buy_now_func' ) );
        		
        		add_action( 'wp_ajax_arm_paid_post_plan_paging_action', array( $this, 'arm_paid_post_plan_paging_action' ) );
        		
        add_action( 'wp_ajax_arm_paid_post_plan_modal_paging_action', array( $this, 'arm_paid_post_plan_modal_paging_action' ) );
        		add_action( 'arm_after_add_transaction', array( $this, 'arm_update_paid_post_transaction' ), 10 );

                add_filter( 'arm_setup_data_before_submit', array( $this, 'arm_add_paid_post_plan_in_setup_data'), 10, 2 );

                

                add_filter( 'arm_notification_add_message_types', array( $this, 'arm_add_paid_post_message_types'), 10 );

                add_action( 'arm_update_access_rules_from_outside', array( $this, 'arm_update_paid_post_access_rules' ), 10 );
		
		add_action('wp_ajax_arm_display_paid_post_cycle', array($this, 'arm_ajax_display_paid_post_cycle'));

                add_action( 'arm_update_access_plan_for_drip_rules', array( $this, 'arm_update_access_plan_for_drip_rules_callback'), 10, 1);

                add_action('wp_ajax_get_arm_paid_post_plan_list', array($this, 'get_arm_paid_post_plan_list_func'));

	        }
        }

        function arm_move_to_trash_paid_post( $post_id ){
            global $ARMember, $wpdb;

            $is_post_exists = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d AND `arm_subscription_plan_is_delete` = %d", $post_id, 0 ) );

            if( isset( $is_post_exists->arm_subscription_plan_id ) &&  '' != $is_post_exists->arm_subscription_plan_id ){

                //update_post_meta( $post_id, 'arm_is_paid_post', 0 );
                update_post_meta( $post_id, 'arm_is_enable_paid_post_before_trash', 1);

                $wpdb->update(
                    $ARMember->tbl_arm_subscription_plans,
                    array(
                        'arm_subscription_plan_is_delete' => 1
                    ),
                    array(
                        'arm_subscription_plan_id' => $is_post_exists->arm_subscription_plan_id
                    )
                );
            }
        }

        function arm_move_to_published_paid_post( $post_id ){
            global $ARMember, $wpdb;

            $is_enabled_before = get_post_meta( $post_id, 'arm_is_enable_paid_post_before_trash', true );

            if( 1 == $is_enabled_before ){

                $is_post_exists = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d AND `arm_subscription_plan_is_delete` = %d", $post_id, 1 ) );

                if( '' != $is_post_exists->arm_subscription_plan_id ){
                    update_post_meta( $post_id, 'arm_is_paid_post', 1 );
                    delete_post_meta( $post_id, 'arm_is_enable_paid_post_before_trash' );

                    $wpdb->update(
                        $ARMember->tbl_arm_subscription_plans,
                        array(
                            'arm_subscription_plan_is_delete' => 0
                        ),
                        array(
                            'arm_subscription_plan_id' => $is_post_exists->arm_subscription_plan_id
                        )
                    );
                }

            }
        }

        function arm_add_pay_per_post_script_data(){

            if( in_array( basename($_SERVER['PHP_SELF']), array( 'post.php', 'post-new.php' ) ) ){
                wp_enqueue_script( 'arm_validate', MEMBERSHIP_URL . '/js/jquery.validate.min.js', array('jquery'), MEMBERSHIP_VERSION );
            }

            if( isset( $_GET['page'] ) && 'arm_manage_pay_per_post' == $_GET['page']  && (!empty($_GET['action']))){
                $this->arm_add_paid_post_metabox_script_data();
            }
        }

        function arm_add_paid_post_metabox_script_data(){
            
            global $arm_payment_gateways;

            $global_currency = $arm_payment_gateways->arm_get_global_currency();
            $all_currencies = $arm_payment_gateways->arm_get_all_currencies();
            $global_currency_sym = isset($all_currencies) ? $all_currencies[strtoupper($global_currency)] : '';
            $global_currency_sym_pos = $arm_payment_gateways->arm_currency_symbol_position($global_currency);
            $global_currency_sym_pos_pre = (!empty($global_currency_sym_pos) && $global_currency_sym_pos == 'prefix' ? '' : 'hidden_section');
            $global_currency_sym_pos_suf = (!empty($global_currency_sym_pos) && $global_currency_sym_pos == 'suffix' ? '' : 'hidden_section');

            $script_data  = 'var CYCLEAMOUNT = "'.esc_html__('Amount', 'ARMember').'";
            var BILLINGCYCLE = "'.esc_html__('Billing Cycle', 'ARMember').'";
            var ARMCYCLELABEL = "'.esc_html__('Label', 'ARMember').'";
            var RECURRINGTIME = "'.esc_html__('Recurring Time', 'ARMember').'";
            var AMOUNTERROR = "'.esc_html__('Amount should not be blank..','ARMember').'";
            var LABELERROR = "'.esc_html__('Label should not be blank..','ARMember').'";
            var DAY = "'.esc_html__('Day(s)', 'ARMember').'";
            var MONTH = "'.esc_html__('Month(s)', 'ARMember').'";
            var YEAR = "'.esc_html__('Year(s)', 'ARMember').'";
            var INFINITE = "'.esc_html__('Infinite', 'ARMember').'";
            var EMESSAGE = "'.esc_html__('You cannot remove all payment cycles.', 'ARMember').'";
            var ARMREMOVECYCLE = "'.esc_html__('Remove Cycle', 'ARMember').'";
            var CURRENCYPREF = "'.$global_currency_sym_pos_pre.'";
            var CURRENCYSUF = "'.$global_currency_sym_pos_suf.'";
            var CURRENCYSYM = "'.$global_currency_sym.'";
            var ARM_RR_CLOSE_IMG = "'.MEMBERSHIP_IMAGES_URL.'/arm_close_icon.png";
            var ARM_RR_CLOSE_IMG_HOVER = "'.MEMBERSHIP_IMAGES_URL.'/arm_close_icon_hover.png";
            var ADDCYCLE = "'.esc_html__('Add Payment Cycle', 'ARMember').'";
            var REMOVECYCLE = "'.esc_html__('Remove Payment Cycle', 'ARMember').'";
            var INVALIDAMOUNTERROR = "'.esc_html__('Please enter valid amount','ARMember').'";
            var ARMEDITORNOTICELABEL = "'.esc_html__('ARMember settings','ARMember').': ";';

            if( function_exists( 'wp_add_inline_script' ) ){
                wp_add_inline_script( 'arm_tinymce', $script_data, 'after' );
            } else {
                echo '<script>' . $script_data . '</script>';
            }
        }

        function arm_add_paid_post_metabox( $post_type, $post ){

            $this->arm_add_paid_post_metabox_script_data();

            add_meta_box(
                'arm_paid_post_metabox_wrapper',
                 esc_html__( 'ARMember Settings', 'ARMember' ),
                 array( $this,'arm_add_paid_post_metabox_callback'), 
                 $post_type,
                 'normal',
                 'high',
                 array(
                     '__block_editor_compatible_meta_box' => true,
                )
            );

        }

        function arm_get_plan_expiry_time( $posted_data ){

            $final_expiry_time = '';

            if( 'buy_now' == $posted_data['paid_post_type'] && 'fixed_duration' == $posted_data['paid_post_duration'] ){
                $duration_type = $posted_data['arm_paid_plan_one_time_duration']['type'];

                $duration_d_time = $posted_data['arm_paid_plan_one_time_duration']['days'];
                $duration_w_time = $posted_data['arm_paid_plan_one_time_duration']['week'];
                $duration_m_time = $posted_data['arm_paid_plan_one_time_duration']['month'];
                $duration_y_time = $posted_data['arm_paid_plan_one_time_duration']['year'];


                if( 'd' == $duration_type ){
                    $timestamp = '+' . $duration_d_time . ' day';
                } else if( 'w' == $duration_type ){
                    $timestamp = '+' . $duration_w_time . ' week';   
                } else if( 'w' == $duration_type ){
                    $timestamp = '+' . $duration_m_time . ' month';
                } else if( 'w' == $duration_type ){
                    $timestamp = '+' . $duration_y_time . ' year';
                } else {
                    $timestamp = '+' . $duration_d_time . ' days';
                }

                $final_expiry_time = date( 'Y-m-d', strtotime( $timestamp ) ) . ' 23:59:59';
            }

            return $final_expiry_time;
        }

        function arm_add_paid_post_data_callback(){

            global $ARMember, $wpdb;

            $response = array('status' => 'error', 'message' => esc_html__('Sorry, Something went wrong. Please try again.', 'ARMember'));

            $post_type = isset( $_POST['arm_add_paid_post_item_type'] ) ? $_POST['arm_add_paid_post_item_type'] : 'page';

            $selected_posts = isset( $_POST['arm_paid_post_item_id'] ) ? $_POST['arm_paid_post_item_id'] : array();

            if( isset( $selected_posts ) && empty( $selected_posts) ){
                $response = array(
                    'status' => 'error',
                    'message' => esc_html__( 'Please select atleast one page/post', 'ARMember' )
                );
            } else {
                $_POST['arm_enable_paid_post'] = 1;
                foreach( $selected_posts as $post_id ){
                    $this->arm_save_paid_post_metabox( $post_id );
                    $this->arm_update_access_rule_for_paid_post( $post_id );
                }

                $message = addslashes( esc_html__('Paid Post added successfully', 'ARMember') );
                $response = array(
                    'status' => 'success',
                    'redirect_to' => admin_url( 'admin.php?page=arm_manage_pay_per_post&status=success&msg=' . $message )
                );
            }
            echo json_encode( $response );
            die;

        }

        function arm_add_update_paid_post() {

            
            if( isset( $_POST['arm_action'] ) && 'arm_add_update_paid_post_plan' == $_POST['arm_action'] ){
                $_POST['arm_enable_paid_post'] = 1;
                if( isset( $_POST['action'] ) && 'edit_paid_post' == $_POST['action'] ){
                    $selected_post = isset( $_POST['edit_paid_post_id'] ) ? $_POST['edit_paid_post_id'] : '';
                    $this->arm_save_paid_post_metabox( $selected_post );
                    $this->arm_update_access_rule_for_paid_post( $selected_post );
                    $message = addslashes( esc_html__('Paid Post updated successfully', 'ARMember') );
                    $paid_post_url = admin_url( 'admin.php?page=arm_manage_pay_per_post&action=edit_paid_post&post_id='.$selected_post.'&status=success&msg=' . $message );
                    wp_redirect( $paid_post_url );
                    die;
                } else {
                    $selected_posts = isset( $_POST['arm_paid_post_item_id'] ) ? $_POST['arm_paid_post_item_id'] : array();
                    foreach( $selected_posts as $post_id ){
                        $this->arm_save_paid_post_metabox( $post_id );
                        $this->arm_update_access_rule_for_paid_post( $post_id );
                    }
                    $message = addslashes( esc_html__('Paid Post added successfully', 'ARMember') );
                    $paid_post_url = admin_url( 'admin.php?page=arm_manage_pay_per_post&status=success&msg=' . $message );
                    wp_redirect( $paid_post_url );
                    die;
                }
            }
        }

        function arm_update_paid_post_data_callback(){
            global $ARMember, $wpdb;

            $response = array('status' => 'error', 'message' => esc_html__('Sorry, Something went wrong. Please try again.', 'ARMember'));

            $post_type = isset( $_POST['arm_edit_post_type'] ) ? $_POST['arm_edit_post_type'] : 'page';

            $selected_post = isset( $_POST['arm_edit_post_id'] ) ? $_POST['arm_edit_post_id'] : '';

            if( empty($selected_post) ){
                $response = array(
                    'status' => 'error',
                    'message' => esc_html__( 'There is something error while updating paid post', 'ARMember' )
                );
            } else {
                $_POST['arm_enable_paid_post'] = 1;
                $this->arm_save_paid_post_metabox( $selected_post );
                $this->arm_update_access_rule_for_paid_post( $selected_post );
                $message = addslashes( esc_html__('Paid Post updated successfully', 'ARMember') );
                $response = array(
                    'status' => 'success',
                    'redirect_to' => admin_url( 'admin.php?page=arm_manage_pay_per_post&status=success&msg=' . $message )
                );
            }

            echo json_encode( $response );
            die;

        }

        function arm_update_access_rule_for_paid_post( $post_id ){

            $isEnablePaidPost = get_post_meta( $post_id, 'arm_is_paid_post', true );

            if( 1 == $isEnablePaidPost ){

                global $ARMember, $wpdb;
                $hasAccessRule = get_post_meta( $post_id, 'arm_access_plan', false );
                $getRow = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d", $post_id ) );

                if( isset( $getRow->arm_subscription_plan_id ) && '' != $getRow->arm_subscription_plan_id ){

                    $plan_id = $getRow->arm_subscription_plan_id;

                    if( isset( $plan_id ) &&  isset( $hasAccessRule ) && is_array( $hasAccessRule ) && in_array( '0', $hasAccessRule ) && !in_array( $plan_id, $hasAccessRule) ){
                        add_post_meta( $post_id, 'arm_access_plan', $plan_id );
                    }
                }


            }

        }

        function arm_save_paid_post_metabox($post_id, $post = array(), $update=false){

            global $ARMember,$wpdb;
            if( empty( $_POST ) ){
                return;
            }

            if (!isset($_POST['arm_enable_paid_post_hidden']) && ( empty($_REQUEST['page']) ) ) {
                //Special condition for WP All Import plugin.
                return;
            }

            if( array_key_exists('arm_enable_paid_post', $_POST ) && ! wp_is_post_revision( $post_id ) ){

                update_post_meta( $post_id, 'arm_is_paid_post', 1 );

                $enable_alternative_content = isset( $_POST['arm_enable_paid_post_alternate_content'] ) ? $_POST['arm_enable_paid_post_alternate_content'] : '';
                update_post_meta( $post_id, 'arm_enable_paid_post_alternate_content', $enable_alternative_content );

                if( !empty( $enable_alternative_content )) {
                    
                    if ( !empty($_POST['arm_paid_post_alternative_content-edit']) ) {
                        $post_alternative_content = isset( $_POST['arm_paid_post_alternative_content-edit'] ) ? $_POST['arm_paid_post_alternative_content-edit'] : '';
                    } else {                        
                        $post_alternative_content = isset( $_POST['arm_paid_post_alternative_content'] ) ? $_POST['arm_paid_post_alternative_content'] : '';
                    }
                    
                    update_post_meta( $post_id, 'arm_paid_post_alternative_content', $post_alternative_content );
                }

                $plan_type = '';
                $plan_options = array();
                $plan_name = isset( $_POST['post_title'] ) ? $_POST['post_title'] : '';

                if( empty($plan_name) ){
                    $postObj = get_post( $post_id );
                    $plan_name = $postObj->post_title;
                }


                $plan_options['pricetext'] = $plan_name;

                if( 'buy_now' == $_POST['paid_post_type'] ){
                    
                    $expiry_date = $this->arm_get_plan_expiry_time( $_POST );

                    if( 'forever' == $_POST['paid_post_duration'] ){
                        $plan_type = 'paid_infinite';
                        $plan_options['access_type'] = 'lifetime';
                        $plan_options['payment_type'] = 'one_time';
                    } else {
                        $plan_type = 'paid_finite';
                        $plan_options['access_type'] = 'finite';
                        $plan_options['payment_type'] = 'one_time';
                        $plan_options['expiry_type'] = 'joined_date_expiry';
                        $plan_options['eopa'] = array(
                            'days' => $_POST['arm_paid_plan_one_time_duration']['days'],
                            'weeks' => $_POST['arm_paid_plan_one_time_duration']['week'],
                            'months' => $_POST['arm_paid_plan_one_time_duration']['month'],
                            'years' => $_POST['arm_paid_plan_one_time_duration']['year'],
                            'type' => $_POST['arm_paid_plan_one_time_duration']['type']
                        );

                        if( '' != $expiry_date ){
                            $plan_options['expiry_date']  = $expiry_date;
                        }
                        $plan_options['eot'] = 'block';
                        $plan_options['grace_period'] = array(
                            'end_of_term' => 0,
                            'failed_payment' => 0
                        );

                        $plan_options['upgrade_action'] = 'immediate';
                        $plan_options['downgrade_action'] = 'on_expire';
                    }
                    $plan_amount = isset( $_POST['arm_paid_post_plan'] ) ? $_POST['arm_paid_post_plan'] : '';
                } else if( 'subscription' == $_POST['paid_post_type'] ){
                    $plan_type = 'recurring';
                    $plan_options['access_type'] = 'finite';
                    $plan_options['payment_type'] = 'subscription';

                    if( !empty($_POST['arm_paid_post_subscription_plan_options']['payment_cycles']) ){
                        $plan_options['payment_cycles'] = array_values( $_POST['arm_paid_post_subscription_plan_options']['payment_cycles'] );
                    } else {
                        $plan_options['payment_cycles'] = array();
                    }

                    $plan_options['trial'] = array(
                        'amount' => 0,
                        'days' => 1,
                        'months' => 1,
                        'years' => 1,
                        'type' => 'D'
                    );


                    $arm_paid_post_data = $plan_options['payment_cycles'][0];

                    $arm_post_days = 1;
                    $arm_post_months = 1;
                    $arm_post_years = 1;

                    if($arm_paid_post_data['billing_type'] == 'D')
                    {
                        $arm_post_days = $arm_paid_post_data['billing_cycle'];
                    }
                    else if($arm_paid_post_data['billing_type'] == 'D')
                    {
                        $arm_post_months = $arm_paid_post_data['billing_cycle'];
                    }
                    else
                    {
                        $arm_post_years = $arm_paid_post_data['billing_cycle'];
                    }

                    //$plan_options['recurring'] = $arm_paid_post_data;
                    $plan_options['recurring'] = array(
                        'days'                 => $arm_post_days,
                        'months'               => $arm_post_months,
                        'years'                => $arm_post_years,
                        'type'                 => $arm_paid_post_data['billing_type'],
                        'time'                 => $arm_paid_post_data['recurring_time'],
                        'manual_billing_start' => 'transaction_day',
                    );
                    $plan_options['cancel_action'] = 'block';
                    $plan_options['cancel_plan_action'] = 'immediate';
                    $plan_options['eot'] = 'block';
                    $plan_options['grace_period'] = array(
                        'end_of_term' => 0,
                        'failed_payment' => 0
                    );

                    $plan_options['payment_failed_action'] = 'block';
                    $plan_options['upgrade_action'] = 'immediate';
                    $plan_options['downgrade_action'] = 'on_expire';
                    $plan_amount = isset( $arm_paid_post_data['cycle_amount'] ) ? $arm_paid_post_data['cycle_amount'] : '';
                } else if( 'free' == $_POST['paid_post_type'] ){
                    $plan_type = 'free';
                    $plan_options['access_type'] = 'lifetime';
                    $plan_options['payment_type'] = 'one_time';
		    $plan_amount = 0;
                }

                $status = 1;
                $plan_role = 'armember';

                $arm_subscription_plan_created_date = current_time('mysql');

                

                $post_data_array = array(
                    'arm_subscription_plan_name' => $plan_name,
                    'arm_subscription_plan_type' => $plan_type,
                    'arm_subscription_plan_options' => maybe_serialize( $plan_options ),
                    'arm_subscription_plan_amount' => $plan_amount,
                    'arm_subscription_plan_status' => $status,
                    'arm_subscription_plan_role'    => $plan_role,
                    'arm_subscription_plan_post_id' => $post_id,
                    'arm_subscription_plan_is_delete' => 0,
                    'arm_subscription_plan_created_date' => $arm_subscription_plan_created_date
                );

                $is_post_exists = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d", $post_id ) );

                if( isset( $is_post_exists->arm_subscription_plan_id ) && '' != $is_post_exists->arm_subscription_plan_id ){
                    $wpdb->update(
                        $ARMember->tbl_arm_subscription_plans,
                        $post_data_array,
                        array(
                            'arm_subscription_plan_id' => $is_post_exists->arm_subscription_plan_id
                        )
                    );
                    $plan_id = $is_post_exists->arm_subscription_plan_id;
                } else {

                    $wpdb->insert(
                        $ARMember->tbl_arm_subscription_plans,
                        $post_data_array
                    );
                    $plan_id = $wpdb->insert_id;
                }

            } else {
                update_post_meta( $post_id, 'arm_is_paid_post', 0 );

                $is_post_exists = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d", $post_id ) );
                if(!empty($is_post_exists))
                {
                    if( '' != $is_post_exists->arm_subscription_plan_id ){

                        delete_post_meta( $post_id, 'arm_access_plan', $is_post_exists->arm_subscription_plan_id );

                        $wpdb->update(
                            $ARMember->tbl_arm_subscription_plans,
                            array(
                                'arm_subscription_plan_is_delete' => 1
                            ),
                            array(
                                'arm_subscription_plan_id' => $is_post_exists->arm_subscription_plan_id
                            )
                        );
                    }
                }
            }

        }

        function arm_add_paid_post_plan_for_restriction( $all_plans, $post_id ){

            global $wpdb, $ARMember;

            $isEnablePaidPost = get_post_meta( $post_id, 'arm_is_paid_post', true );

            if( 1 == $isEnablePaidPost ){

                $isRestricted = get_post_meta( $post_id, 'arm_access_plan', true );

                $getPlanId = $this->arm_get_plan_from_post_id( $post_id );


                if( '0' == $isRestricted && !empty( $getPlanId ) ){
                    $plan_id = $getPlanId;

                    if( empty($all_plans) ){
                        $all_plans = $plan_id;
                    } else {

                        $all_plans .= ',' . $plan_id;

                    }

                }

            }

            return $all_plans;

        }

        function arm_add_paid_post_metabox_callback( $post_obj, $metabox_data ){

            return $this->arm_add_paid_post_metabox_html( $post_obj, $metabox_data );
        }


        function arm_add_paid_post_metabox_html( $post_obj, $metabox_data, $paid_post_page = false, $return = false ) {

            global $arm_payment_gateways,$ARMember,$wpdb, $arm_global_settings;

            /* Add CSS for Metaboxes */
            wp_enqueue_style('arm_post_metaboxes_css', MEMBERSHIP_URL . '/css/arm_post_metaboxes.css', array(), MEMBERSHIP_VERSION);

            $global_currency = $arm_payment_gateways->arm_get_global_currency();
            $all_currencies = $arm_payment_gateways->arm_get_all_currencies();
            $global_currency_sym = isset($all_currencies) ? $all_currencies[strtoupper($global_currency)] : '';

            $global_currency_sym_pos = $arm_payment_gateways->arm_currency_symbol_position($global_currency);
            $global_currency_sym_pos_pre = ( !empty( $global_currency_sym_pos ) && $global_currency_sym_pos == 'prefix' ? '' : 'hidden_section' );
            $global_currency_sym_pos_suf = ( !empty( $global_currency_sym_pos ) && $global_currency_sym_pos == 'suffix' ? '' : 'hidden_section' );

            $payment_cycles_data = array();

            $post_id = isset( $post_obj->ID ) ? $post_obj->ID : '';

            $is_paid_post_enabled = get_post_meta( $post_id, 'arm_is_paid_post', true );

            $total_paid_post_setups = $this->arm_get_paid_post_setup();

            if( $total_paid_post_setups < 1 && ! $paid_post_page ){

                $paid_post_html = '<div class="arm_paid_post_container">';

                    $arm_setup_link = admin_url( 'admin.php?page=arm_membership_setup&action=new_setup' );

                    $paid_post_html .= '<div class="arm_paid_post_notice">'. sprintf( esc_html__( 'You don\'t have created paid post type membership setup. Please create at least one membership setup for paid post from %s and then reload this page.', 'ARMember' ), '<a href="'.$arm_setup_link.'">here</a>' ).'</div>';

                $paid_post_html .= '</div>';

                echo $paid_post_html;

                return;
            }

            if( $post_id ){
                $paid_post_plan_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d", $post_id) );
            } else {
                $paid_post_plan_data = new stdClass();
            }

            $plan_type = isset( $paid_post_plan_data->arm_subscription_plan_type ) ? $paid_post_plan_data->arm_subscription_plan_type : 'paid_infinite';

            $plan_options = isset( $paid_post_plan_data->arm_subscription_plan_options ) ? maybe_unserialize( $paid_post_plan_data->arm_subscription_plan_options ) : array();

            if( isset( $plan_options['payment_cycles'] ) && !empty( $plan_options['payment_cycles'] ) ){
                $payment_cycles_data = $plan_options['payment_cycles'];
            }

            if( !isset( $payment_cycles_data ) || empty( $payment_cycles_data ) ){
                $payment_cycles_data[] = array(
                    'cycle_key' => 'arm0',
                    'cycle_label' => '',
                    'cycle_amount' => '',
                    'billing_cycle' => 1,
                    'billing_type' => 'D',
                    'recurring_time' => 'infinite',
                    'payment_cycle_order' => 1
                );
            }
           
            $paid_post_html  = '<div class="arm_paid_post_container">';

                if( !$paid_post_page ){

                    $paid_post_html .= '<div class="arm_paid_post_row arm_paid_post_no_margin">';

                        $paid_post_html .= '<div class="arm_paid_post_row_left">' . esc_html__('Enable Pay Per Post', 'ARMember') . '</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row_right">';

                            $paid_post_html .= '<input type="hidden" value="'.$is_paid_post_enabled.'" name="arm_enable_paid_post_hidden" id="arm_enable_paid_post_hidden" />';

                            $paid_post_html .= '<div class="armswitch armswitchbig">';

                                $enable_paid_post = checked( 1, $is_paid_post_enabled, false );
                                
                                $paid_post_html .= '<input type="checkbox" value="1" '.$enable_paid_post.' class="armswitch_input" name="arm_enable_paid_post" id="arm_enable_paid_post" />';

                                $paid_post_html .= '<label for="arm_enable_paid_post" class="armswitch_label"></label>';

                                $paid_post_html .= '<div class="armclear"></div>';

                            $paid_post_html .= '</div>';

                        $paid_post_html .= '</div>';

                    $paid_post_html .= '</div>';
                }

                $arm_show_paid_post_container = 'hidden_section';

                if( $is_paid_post_enabled || $paid_post_page ){
                    $arm_show_paid_post_container = '';
                }
                $paid_post_html .= '<div class="arm_paid_post_items_list_container" id="arm_paid_post_items_list_container"></div>';
                $paid_post_html .= '<div class="arm_paid_post_inner_container '.$arm_show_paid_post_container.'">';

                    if( $paid_post_page ){

                        if( isset( $_GET['action'] ) && 'edit_paid_post' == $_GET['action'] ){

                            $paid_post_html .= '<div class="arm_paid_post_row">';

                                $paid_post_html .= '<div class="arm_paid_post_row_left">' . esc_html__( 'Paid Post Title', 'ARMember' ) . '</div>';

                                $paid_post_html .= '<div class="arm_paid_post_row_right">&nbsp;&nbsp;' . $post_obj->post_title . '</div>';

                            $paid_post_html .= '</div>';

                        } else {

                            $paid_post_html .= '<div class="arm_paid_post_row">';

                                $paid_post_html .= '<div class="arm_paid_post_row_left">' . esc_html__('Content Type', 'ARMember') . '</div>';

                                $paid_post_html .= '<div class="arm_paid_post_row_right">';

                                    $post_type = isset( $post_obj->post_type ) ? $post_obj->post_type : 'page';

                                    $PaidPostContentTypes = array('page' => __('Page', 'ARMember'), 'post' => __('Post', 'ARMember'));

                                    $custom_post_types = get_post_types(array('public' => true, '_builtin' => false, 'show_ui' => true), 'objects');

                                    if( !in_array( $post_type, array( 'page', 'post' ) ) ){
                                        $post_type_label = $custom_post_types[$post_type]->label;
                                    } else {
                                        $post_type_label = $PaidPostContentTypes[$post_type];
                                    }

                                    $paid_post_html .= '<input type="hidden" id="arm_add_paid_post_item_type" class="arm_paid_post_item_type_input" name="arm_add_paid_post_item_type" data-type="'.$post_type_label.'" value="'.$post_type.'"/>';

                                    $paid_post_html .= '<dl class="arm_selectbox">';

                                        $paid_post_html .= '<dt style="width: 480px;"><span>'.$post_type_label.'</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';

                                        $paid_post_html .= '<dd>';

                                            $paid_post_html .= '<ul data-id="arm_add_paid_post_item_type">';
                                                
                                                if (!empty($custom_post_types)) {
                                                
                                                    foreach ($custom_post_types as $cpt) {
                                                
                                                        $PaidPostContentTypes[$cpt->name] = $cpt->label;
                                                
                                                    }
                                                
                                                }

                                                if (!empty($PaidPostContentTypes)) {

                                                    foreach ($PaidPostContentTypes as $key => $val) {

                                                        $paid_post_html .= '<li data-label="' . $val .'" data-value="' . $key .'" data-type="' . $val .'">' . $val .'</li>';

                                                    }
                                                }

                                            $paid_post_html .= '</ul>';

                                        $paid_post_html .= '</dd>';

                                    $paid_post_html .= '</dl>';

                                $paid_post_html .= '</div>';

                            $paid_post_html .= '</div>';

                            $paid_post_html .= '<div class="arm_paid_post_row">';

                                $paid_post_html .= '<div class="arm_paid_post_row_left arm_paid_post_row_select_post_type_label arm_paid_post_row_float_left">' . esc_html__('Select', 'ARMember'). ' <span class="arm_paid_post_item_type_text">' . $post_type_label .'</span> *</div>';

                                $paid_post_html .= '<div class="arm_paid_post_row_right">';

                                    $paid_post_html .= '<div style="text-align: center;width: 100%;"><img src="'.MEMBERSHIP_IMAGES_URL.'/arm_loader.gif" id="arm_loader_img_paid_post_items" class="arm_loader_img_paid_post_items" style="display: none;" width="20" height="20" /></div>';

                                    $paid_post_html .= '<input id="arm_paid_post_items_input" type="text" value="" placeholder="'. esc_html__( 'Search by title...', 'ARMember').'" required data-msg-required="'.esc_html__('Please select atleast one page/post.', 'ARMember').'" />';

                                    $paid_post_html .= '<div class="arm_paid_post_items arm_required_wrapper" id="arm_paid_post_items" style="display: none;"></div>';

                                $paid_post_html .= '</div>';

                            $paid_post_html .= '</div>';

                        }

                    }

                    $paid_post_html .= '<div class="arm_paid_post_row">';

                        $paid_post_html .= '<div class="arm_paid_post_row_left">' . esc_html__('Post Type', 'ARMember') . '</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row_right">';
                            $free_post_checked = '';
                            if( 'free' == $plan_type ){
                                $free_post_checked =  ' checked="checked" ';
                            }

                            $paid_post_html .= '<input type="radio" '.$free_post_checked.' name="paid_post_type" id="arm_paid_free_post" value="free" class="arm_iradio" />';

                            $paid_post_html .= '<label class="form_popup_type_radio" for="arm_paid_free_post">';

                                $paid_post_html .= '&nbsp;'.esc_html__('Free Post','ARMember');

                            $paid_post_html .= '</label>';

                            $paid_post_html .= '&nbsp;';
                            

                            $buy_now_checked = '';
                            if( 'paid_infinite' == $plan_type || 'paid_finite' == $plan_type ){
                                $buy_now_checked = ' checked="checked" ';
                            }

                            $paid_post_html .= '<input type="radio" '.$buy_now_checked.' name="paid_post_type" id="arm_paid_post_buynow" value="buy_now" class="arm_iradio" />';

                            $paid_post_html .= '<label class="form_popup_type_radio" for="arm_paid_post_buynow">';

                                $paid_post_html .= '&nbsp;'.esc_html__('Buy Now','ARMember');

                            $paid_post_html .= '</label>';

                            $paid_post_html .= '&nbsp;';

                            $subscription_checked = '';

                            if( 'recurring' == $plan_type ){
                                $subscription_checked =  ' checked="checked" ';
                            }

                            $paid_post_html .= '<input type="radio" '.$subscription_checked.' name="paid_post_type" id="arm_paid_post_subscription" value="subscription" class="arm_iradio" />';

                            $paid_post_html .= '<label class="form_popup_type_radio" for="arm_paid_post_subscription">';

                                $paid_post_html .= '&nbsp;'.esc_html__('Subscription/Recurring Plan','ARMember');

                            $paid_post_html .= '</label>';

                        $paid_post_html .= '</div>';

                    $paid_post_html .= '</div>';

                    $show_paid_post_amount = ' hidden_section ';
                    $show_paid_post_duration = ' hidden_section ';
                    if( empty($plan_type) || 'paid_infinite' == $plan_type || 'paid_finite' == $plan_type ){
                        $show_paid_post_amount = '';
                        $show_paid_post_duration = '';
                    }

                    $paid_post_html .= '<div class="arm_paid_post_row arm_paid_post_one_time_amount '.$show_paid_post_amount.'">';

                        $paid_post_html .= '<div class="arm_paid_post_row_left">' . esc_html__( 'Paid Post Amount', 'ARMember' ) . ' *</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row_right">';

                            $paid_post_html .= '<span class="arm_paid_post_plan_currency_symbol arm_paid_post_plan_currency_symbol_pre ' . $global_currency_sym_pos_pre . '">'.$global_currency_sym.'</span>';

                            $paid_post_html .= '<input type="text" name="arm_paid_post_plan" class="arm_paid_post_input_field" value="'. ( isset( $paid_post_plan_data->arm_subscription_plan_amount ) ? $paid_post_plan_data->arm_subscription_plan_amount : '' ).'"  onkeypress="javascript:return ArmNumberValidation(event, this)" data-msg-required="'. esc_html__('Amount should not be blank.','ARMember') . '" />';

                            $paid_post_html .= '<span class="arm_paid_post_plan_currency_symbol arm_paid_post_plan_currency_symbol_post ' . $global_currency_sym_pos_suf . '">'.$global_currency_sym.'</span>';

                        $paid_post_html .= '</div>';

                    $paid_post_html .= '</div>';

                    $paid_post_html .= '<div class="arm_paid_post_row arm_paid_post_plan_one_time_duration '.$show_paid_post_duration.' ">';

                        $paid_post_html .= '<div class="arm_paid_post_row_left">' . esc_html__('Duration Type','ARMember') . '</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row_right">';

                            $checked_forever_type = '';
                            if( !isset( $plan_options['access_type'] ) || ( isset( $plan_options['access_type'] ) && 'lifetime' == $plan_options['access_type'] ) ){
                                $checked_forever_type = ' checked="checked" ';
                            }

                            $paid_post_html .= '<input type="radio" name="paid_post_duration" id="arm_paid_post_duration_lifetime" value="forever" class="arm_iradio" ' . $checked_forever_type . ' />';

                            $paid_post_html .= '<label class="form_popup_type_radio" for="arm_paid_post_duration_lifetime">';
                                $paid_post_html .= '&nbsp;'.esc_html__('Lifetime','ARMember');

                            $paid_post_html .= '</label>';

                            $paid_post_html .= '&nbsp;';

                            $checked_fixed_duration_type = '';
                            if( isset( $plan_options['access_type'] ) && 'finite' == $plan_options['access_type'] ){
                                $checked_fixed_duration_type = ' checked="checked" ';
                            }

                            $paid_post_html .= '<input type="radio" name="paid_post_duration" id="arm_paid_post_duration_fixed" value="fixed_duration" class="arm_iradio" ' . $checked_fixed_duration_type . ' />';

                            $paid_post_html .= '<label class="form_popup_type_radio" for="arm_paid_post_duration_fixed">';

                                $paid_post_html .= '&nbsp;'.esc_html__('Fixed Duration','ARMember');

                            $paid_post_html .= '</label>';

                        $paid_post_html .= '</div>';

                    $paid_post_html .= '</div>';

                    $arm_show_fixed_duration = ' hidden_section ';
                    if( isset( $plan_options['access_type'] ) && 'finite' == $plan_options['access_type'] && 'recurring' != $plan_type ){
                        $arm_show_fixed_duration = '';
                    }

                    $paid_post_html .= '<div class="arm_paid_post_row arm_paid_post_plan_one_time_duration_value '.$arm_show_fixed_duration.'">'; 

                        $paid_post_html .= '<div class="arm_paid_post_row_left">' . esc_html__('Duration', 'ARMember') . '</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row_right">';

                            $arm_show_day_duration = ' hidden_section ';
                            if( ! isset( $plan_options['eopa']['type'] ) || ( isset( $plan_options['eopa']['type'] ) && 'D' == $plan_options['eopa']['type'] ) ){
                                $arm_show_day_duration = '';
                            }

                            $paid_post_html .= '<div class="arm_paid_post_duration_dropdown arm_paid_post_duration_d ' . $arm_show_day_duration . '">';

                                $paid_post_html .= '<div class="arm_paid_post_selectbox_wrapper">';

                                    $paid_post_html .= '<input type="hidden" name="arm_paid_plan_one_time_duration[days]" value="' . ( isset( $plan_options['eopa']['days'] ) ? $plan_options['eopa']['days'] : 1 ) . '" id="arm_paid_plan_one_time_duration_d" />';

                                    $paid_post_html .= '<dl class="arm_selectbox">';

                                        $paid_post_html .= '<dt style="width:100px;"><span>' . ( isset( $plan_options['eopa']['days'] ) ? $plan_options['eopa']['days'] : 1 ) . '</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';

                                        $paid_post_html .= '<dd>';
                                                    
                                            $paid_post_html .= '<ul data-id="arm_paid_plan_one_time_duration_d">';
                                                
                                                for ($i = 1; $i <= 90; $i++) {
                                                    $paid_post_html .= '<li data-label="' . $i . '" data-value="'. $i . '">'. $i. '</li>';
                                                }

                                            $paid_post_html .= '</ul>';

                                        $paid_post_html .= '</dd>';

                                    $paid_post_html .= '</dl>';

                                $paid_post_html .= '</div>';

                            $paid_post_html .= '</div>';

                            $arm_show_week_duration = ' hidden_section ';
                            if( isset( $plan_options['eopa']['type'] ) && 'W' == $plan_options['eopa']['type'] ){
                                $arm_show_week_duration = '';
                            }
                            $paid_post_html .= '<div class="arm_paid_post_duration_dropdown arm_paid_post_duration_w ' . $arm_show_week_duration . '">';

                                $paid_post_html .= '<div class="arm_paid_post_selectbox_wrapper">';

                                    $paid_post_html .= '<input type="hidden" name="arm_paid_plan_one_time_duration[week]" value="' . ( isset( $plan_options['eopa']['weeks'] ) ? $plan_options['eopa']['weeks'] : 1 ) . '" id="arm_paid_plan_one_time_duration_w" />';

                                    $paid_post_html .= '<dl class="arm_selectbox">';

                                        $paid_post_html .= '<dt style="width:100px;"><span>' . ( isset( $plan_options['eopa']['weeks'] ) ? $plan_options['eopa']['weeks'] : 1 ) . '</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';

                                        $paid_post_html .= '<dd>';
                                                    
                                            $paid_post_html .= '<ul data-id="arm_paid_plan_one_time_duration_w">';
                                                
                                                for ($i = 1; $i <= 52; $i++) {
                                                    $paid_post_html .= '<li data-label="' . $i . '" data-value="'. $i . '">'. $i. '</li>';
                                                }

                                            $paid_post_html .= '</ul>';

                                        $paid_post_html .= '</dd>';

                                    $paid_post_html .= '</dl>';

                                $paid_post_html .= '</div>';

                            $paid_post_html .= '</div>';

                            $arm_show_month_duration = ' hidden_section ';
                            if( isset( $plan_options['eopa']['type'] ) && 'M' == $plan_options['eopa']['type'] ){
                                $arm_show_month_duration = '';
                            }

                            $paid_post_html .= '<div class="arm_paid_post_duration_dropdown arm_paid_post_duration_m ' . $arm_show_month_duration . '">';

                                $paid_post_html .= '<div class="arm_paid_post_selectbox_wrapper">';

                                    $paid_post_html .= '<input type="hidden" name="arm_paid_plan_one_time_duration[month]" value="' . ( isset( $plan_options['eopa']['months'] ) ? $plan_options['eopa']['months'] : 1 ) . '" id="arm_paid_plan_one_time_duration_m" />';

                                    $paid_post_html .= '<dl class="arm_selectbox">';

                                        $paid_post_html .= '<dt style="width:100px;"><span>' . ( isset( $plan_options['eopa']['months'] ) ? $plan_options['eopa']['months'] : 1 ) . '</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';

                                        $paid_post_html .= '<dd>';
                                                    
                                            $paid_post_html .= '<ul data-id="arm_paid_plan_one_time_duration_m">';
                                                
                                                for ($i = 1; $i <= 24; $i++) {
                                                    $paid_post_html .= '<li data-label="' . $i . '" data-value="'. $i . '">'. $i. '</li>';
                                                }

                                            $paid_post_html .= '</ul>';

                                        $paid_post_html .= '</dd>';

                                    $paid_post_html .= '</dl>';

                                $paid_post_html .= '</div>';

                            $paid_post_html .= '</div>';

                            $arm_show_year_duration = ' hidden_section';
                            if( isset( $plan_options['eopa']['type'] ) && 'Y' == $plan_options['eopa']['type'] ){
                                $arm_show_year_duration = '';
                            }

                            $paid_post_html .= '<div class="arm_paid_post_duration_dropdown arm_paid_post_duration_y ' . $arm_show_year_duration . '">';

                                $paid_post_html .= '<div class="arm_paid_post_selectbox_wrapper">';

                                    $paid_post_html .= '<input type="hidden" name="arm_paid_plan_one_time_duration[year]" value="' . ( isset( $plan_options['eopa']['years'] ) ? $plan_options['eopa']['years'] : 1 ) . '" id="arm_paid_plan_one_time_duration_y" />';

                                    $paid_post_html .= '<dl class="arm_selectbox">';

                                        $paid_post_html .= '<dt style="width:100px;"><span>' . ( isset( $plan_options['eopa']['years'] ) ? $plan_options['eopa']['years'] : 1 ) . '</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';

                                        $paid_post_html .= '<dd>';
                                                    
                                            $paid_post_html .= '<ul data-id="arm_paid_plan_one_time_duration_y">';
                                                
                                                for ($i = 1; $i <= 15; $i++) {
                                                    $paid_post_html .= '<li data-label="' . $i . '" data-value="'. $i . '">'. $i. '</li>';
                                                }

                                            $paid_post_html .= '</ul>';

                                        $paid_post_html .= '</dd>';

                                    $paid_post_html .= '</dl>';

                                $paid_post_html .= '</div>';

                            $paid_post_html .= '</div>';

                            $paid_post_html .= '&nbsp;&nbsp;';

                            $paid_post_html .= '<div class="arm_paid_post_duration_dropdown">';

                                $paid_post_html .= '<div class="arm_paid_post_selectbox_wrapper">';

                                    $paid_post_html .= '<input type="hidden" name="arm_paid_plan_one_time_duration[type]" value="' . ( isset( $plan_options['eopa']['type'] )? $plan_options['eopa']['type'] : 'D' ) . '" id="arm_paid_plan_one_time_duration_type" />';

                                    $paid_post_html .= '<dl class="arm_selectbox">';

                                        $arm_paid_post_duration_label = esc_html__( 'Day(s)', 'ARMember' );
                                        if( isset( $plan_options['eopa']['type'] ) && 'W' == $plan_options['eopa']['type'] ){
                                            $arm_paid_post_duration_label = esc_html__( 'Week(s)', 'ARMember' );
                                        } else if( isset( $plan_options['eopa']['type'] ) && 'M' == $plan_options['eopa']['type'] ){
                                            $arm_paid_post_duration_label = esc_html__( 'Month(s)', 'ARMember' );
                                        } else if( isset( $plan_options['eopa']['type'] ) && 'Y' == $plan_options['eopa']['type'] ){
                                            $arm_paid_post_duration_label = esc_html__( 'Year(s)', 'ARMember' );
                                        }

                                        $paid_post_html .= '<dt style="width:100px;"><span>' . $arm_paid_post_duration_label . '</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';

                                        $paid_post_html .= '<dd>';

                                            $paid_post_html .= '<ul data-id="arm_paid_plan_one_time_duration_type">';

                                                $paid_post_html .= '<li data-label="'.esc_html__( 'Day(s)', 'ARMember' ).'" data-value="D">'. esc_html__( 'Day(s)', 'ARMember' ) .'</li>';
                                                $paid_post_html .= '<li data-label="'.esc_html__( 'Week(s)', 'ARMember' ).'" data-value="W">'. esc_html__( 'Week(s)', 'ARMember' ) .'</li>';
                                                $paid_post_html .= '<li data-label="'.esc_html__( 'Month(s)', 'ARMember' ).'" data-value="M">'. esc_html__( 'Month(s)', 'ARMember' ) .'</li>';
                                                $paid_post_html .= '<li data-label="'.esc_html__( 'Year(s)', 'ARMember' ).'" data-value="Y">'. esc_html__( 'Year(s)', 'ARMember' ) .'</li>';

                                            $paid_post_html .= '</ul>';

                                        $paid_post_html .= '</dd>';

                                    $paid_post_html .= '</dl>';

                                $paid_post_html .= '</div>';

                            $paid_post_html .= '</div>';

                        $paid_post_html .= '</div>';

                    $paid_post_html .= '</div>';

                    $arm_show_payment_cycles = ' hidden_section ';
                    if( 'recurring' == $plan_type ){
                        $arm_show_payment_cycles = '';
                    }

                    $paid_post_html .= '<div class="arm_paid_post_row arm_paid_post_plan_subscription_cycle ' . $arm_show_payment_cycles . '">';

                        $paid_post_html .= '<div class="arm_paid_post_row_left arm_paid_post_row_float_left">' . esc_html__( 'Payment Cycles', 'ARMember' ) . ' *</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row_right">';

                            $paid_post_html .= '<div class="arm_paid_post_subscription_options_recurring_payment_cycles_main_box">';

                                $paid_post_html .= '<ul class="arm_plan_payment_cycle_ul">';

                                    $total_inirecurring_cycle = count($payment_cycles_data); 
                                    $gi = 1;
                                    foreach( $payment_cycles_data as $arm_pc => $arm_value ){

                                        $paid_post_html .= '<li class="arm_plan_payment_cycle_li paid_subscription_options_recurring_payment_cycles_child_box" id="paid_subscription_options_recurring_payment_cycles_child_box'.$arm_pc.'">';

                                            $paid_post_html .= '<div class="arm_plan_payment_cycle_label">';

                                                $paid_post_html .= '<label class="arm_plan_payment_cycle_label_text">'.esc_html__('Label', 'ARMember').'</label>';

                                                $paid_post_html .= '<div class="arm_plan_payment_cycle_label_input">';

                                                    $paid_post_html .= '<input type="hidden" name="arm_paid_post_subscription_plan_options[payment_cycles]['.$arm_pc.'][cycle_key]" value="' . ( !empty($arm_value['cycle_key']) ? $arm_value['cycle_key'] : 'arm'.rand() ). '"/>';

                                                    $paid_post_html .= '<input type="text" class="arm_paid_post_input_field paid_subscription_options_recurring_payment_cycle_label" name="arm_paid_post_subscription_plan_options[payment_cycles]['.$arm_pc.'][cycle_label]" data-msg-required="'.esc_html__('Label should not be blank', 'ARMember').'" value="' . ( !empty($arm_value['cycle_label']) ? $arm_value['cycle_label'] : '' ). '" />';

                                                $paid_post_html .= '</div>';

                                            $paid_post_html .= '</div>';

                                            $paid_post_html .= '<div class="arm_plan_payment_cycle_amount">';

                                                $paid_post_html .= '<label class="arm_plan_payment_cycle_amount_text">' . esc_html__( 'Amount', 'ARMember' ) . '</label>';

                                                $paid_post_html .= '<div class="arm_plan_payment_cycle_amount_input">';

                                                    $paid_post_html .= '<span class="arm_paid_post_plan_currency_symbol arm_paid_post_plan_currency_symbol_pre '.$global_currency_sym_pos_pre.'">' . $global_currency_sym . '</span>';

                                                    $paid_post_html .= '<input type="text" name="arm_paid_post_subscription_plan_options[payment_cycles]['.$arm_pc.'][cycle_amount]" value="'. (!empty($arm_value['cycle_amount']) ? $arm_value['cycle_amount'] : '' ).'" class="paid_subscription_options_recurring_payment_cycle_amount arm_paid_post_input_field" data-msg-required="'.esc_html__('Amount should not be blank.', 'ARMember').'" onkeypress="javascript:return ArmNumberValidation(event, this)" />';

                                                    $paid_post_html .= '<span class="arm_paid_post_plan_currency_symbol arm_paid_post_plan_currency_symbol_post '.$global_currency_sym_pos_suf.'">' . $global_currency_sym . '</span>';

                                                $paid_post_html .= '</div>';

                                            $paid_post_html .= '</div>';

                                            $paid_post_html .= '<div class="arm_plan_payment_cycle_billing_cycle">';

                                                $paid_post_html .= '<label class="arm_plan_payment_cycle_billing_text">' . esc_html__( 'Billing Cycle', 'ARMember' ) . '</label>';

                                                $paid_post_html .= '<div class="arm_plan_payment_cycle_billing_input">';

                                                    $paid_post_html .= '<input type="hidden" id="arm_ipc_billing'.$arm_pc.'" name="arm_paid_post_subscription_plan_options[payment_cycles]['.$arm_pc.'][billing_cycle]" value="'.(!empty($arm_value['billing_cycle']) ? $arm_value['billing_cycle'] : 1).'" />';

                                                    $paid_post_html .= '<dl class="arm_selectbox" style="margin: 0px;">';

                                                        $paid_post_html .= '<dt style="width: 60px; min-width: 50px;"><span>'.( !empty( $arm_value['billing_cycle'] ) ? $arm_value['billing_cycle'] : 1 ).'</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                                                            
                                                        $paid_post_html .= '<dd>';

                                                            $paid_post_html .= '<ul data-id="arm_ipc_billing' . $arm_pc . '">';
                                                                
                                                                for ($i = 1; $i <= 90; $i++) {

                                                                    $paid_post_html .= '<li data-label="' . $i .'" data-value="' . $i . '">' . $i . '</li>';
                                                                
                                                                }
                                                                 
                                                            $paid_post_html .= '</ul>';

                                                        $paid_post_html .= '</dd>';

                                                    $paid_post_html .= '</dl>';

                                                    $paid_post_html .= '&nbsp;&nbsp;';

                                                    $paid_post_html .= '<input type="hidden" id="arm_ipc_billing_type'.$arm_pc.'" name="arm_paid_post_subscription_plan_options[payment_cycles]['.$arm_pc.'][billing_type]" value="' . ( !empty( $arm_value['billing_type'] ) ? $arm_value['billing_type'] : "D" ) . '" />';

                                                    $paid_post_html .= '<dl class="arm_selectbox" style="margin: 0px;">';

                                                        $paid_post_html .= '<dt style="width: 83px; min-width: 75px; "><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';

                                                        $paid_post_html .= '<dd>';

                                                            $paid_post_html .= '<ul data-id="arm_ipc_billing_type'.$arm_pc.'">';

                                                                $paid_post_html .= '<li data-label="'.esc_html__('Day(s)', 'ARMember').'" data-value="D">'.esc_html__('Day(s)', 'ARMember').'</li>';
                                                                $paid_post_html .= '<li data-label="'.esc_html__('Month(s)', 'ARMember').'" data-value="M">'.esc_html__('Month(s)', 'ARMember').'</li>';
                                                                $paid_post_html .= '<li data-label="'.esc_html__('Year(s)', 'ARMember').'" data-value="Y">'.esc_html__('Year(s)', 'ARMember').'</li>';

                                                            $paid_post_html .= '</ul>';

                                                        $paid_post_html .= '</dd>';

                                                    $paid_post_html .= '</dl>';

                                                $paid_post_html .= '</div>';

                                            $paid_post_html .= '</div>';

                                            $paid_post_html .= '<div class="arm_plan_payment_cycle_recurring_time">';

                                                $paid_post_html .= '<label class="arm_plan_payment_cycle_recurring_text">' . esc_html__('Recurring Time', 'ARMember') . '</label>';

                                                $paid_post_html .= '<input type="hidden" id="arm_ipc_recurring'.$arm_pc.'" name="arm_paid_post_subscription_plan_options[payment_cycles]['.$arm_pc.'][recurring_time]" value="' . (!empty($arm_value['recurring_time']) ? $arm_value['recurring_time'] : 'infinite' ).'" />';

                                                $paid_post_html .= '<dl class="arm_selectbox" style="margin: 0px;">';

                                                    $paid_post_html .= '<dt style="width: 70px; min-width: 70px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';

                                                    $paid_post_html .= '<dd>';

                                                        $paid_post_html .= '<ul data-id="arm_ipc_recurring' . $arm_pc . '">';

                                                            $paid_post_html .= '<li data-label="' . esc_html__('Infinite', 'ARMember') . '" data-value="infinite">' . esc_html__('Infinite', 'ARMember') . '</li>';

                                                            for ($i = 2; $i <= 30; $i++) {

                                                                $paid_post_html .= '<li data-label="' . $i . '" data-value="'. $i . '">' . $i . '</li>';

                                                            }

                                                        $paid_post_html .= '</ul>';

                                                    $paid_post_html .= '</dd>';

                                                $paid_post_html .= '</dl>';

                                            $paid_post_html .= '</div>';

                                            $paid_post_html .= '<div class="arm_plan_payment_cycle_action_buttons">';
                                                
                                                $paid_post_html .= '<div class="arm_plan_cycle_plus_icon arm_helptip_icon tipso_style arm_add_plan_icon" title="'.esc_html__('Add Payment Cycle','ARMember').'" id="arm_add_payment_cycle_recurring" data-field_index="'. ( isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1 ).'"></div>';

                                                $paid_post_html .= '<div class="arm_plan_cycle_minus_icon arm_helptip_icon tipso_style arm_add_plan_icon" title="'.esc_html__('Remove Payment Cycle', 'ARMember').'" id="arm_remove_recurring_payment_cycle" data_index="'. ( isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1 ).'"></div>';
                                                
                                                $paid_post_html .= '<div class="arm_plan_cycle_sortable_icon ui-sortable-handle"></div>';
                                                        
                                            $paid_post_html .= '</div>';

                                            $paid_post_html .= '<input type="hidden" name="arm_paid_post_subscription_plan_options[payment_cycles]['.$arm_pc.'][payment_cycle_order]" value="'.$gi.'" class="arm_module_payment_cycle_order" />';

                                        $paid_post_html .= '</li>';


                                        $gi++;
                                    }

                                $paid_post_html .= '</ul>';

                                $paid_post_html .= '<div class="paid_subscription_options_recurring_payment_cycles_link">';

                                    $paid_post_html .= '<input type="hidden" name="arm_total_recurring_plan_cycles" id="arm_total_recurring_plan_cycles" value="'. ( isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1 ).'"/>';

                                    $paid_post_html .= '<input type="hidden" name="arm_total_recurring_plan_cycles" id="arm_total_recurring_plan_cycles_order" value="2"/>';

                                    $paid_post_html .= '<input type="hidden" name="arm_total_recurring_plan_cycles_counter" id="arm_total_recurring_plan_cycles_counter" value="'. ( isset($total_inirecurring_cycle) ? $total_inirecurring_cycle : 1 ).'"/>';

                                $paid_post_html .= '</div>';

                            $paid_post_html .= '</div>';

                        $paid_post_html .= '</div>';

                    $paid_post_html .= '</div>';

                    $paid_post_html .= '<div class="arm_paid_post_row_separator"></div>';

                    $paid_post_html .= '<div class="arm_paid_post_row">';

                        $paid_post_html .= '<div class="arm_paid_post_row_left">' . esc_html__( 'Alternative Content', 'ARMember' ). '</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row_right">';

                            $enable_ppost_alternate_content = get_post_meta( $post_id, 'arm_enable_paid_post_alternate_content', true );

                            $checked_paid_post_alt_content = checked( 1, $enable_ppost_alternate_content, false );

                            $paid_post_html .= '<div class="armswitch armswitchbig">';

                            $alternate_switch_text = '';

                            if( isset( $_GET['action'] ) && 'edit_paid_post' == $_GET['action'] ){
                                $alternate_switch_text = '-edit';
                            }
                            
                            $paid_post_html .= '<input type="checkbox" value="1" ' . $checked_paid_post_alt_content . ' class="armswitch_input" name="arm_enable_paid_post_alternate_content" id="arm_enable_ppost_alternate_content'.$alternate_switch_text.'" />';

                            $paid_post_html .= '<label for="arm_enable_ppost_alternate_content'.$alternate_switch_text.'" class="armswitch_label"></label>';

                            $paid_post_html .= '<div class="armclear"></div>';

                        $paid_post_html .= '</div>';
                        $paid_post_html .= '</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row">';
                            $paid_post_html .= '<div class="arm_paid_post_row_left">&nbsp;</div>';
                            $paid_post_html .= '<div class="arm_paid_post_row_right">';
                                $paid_post_html .= '<span class="arm_info_text" style="margin: 10px 0 0;">'. __("Display alternative content to the member who has not buy this post. If this disable then default content will be displayed from ARMember -> General Settings -> Paid Post Settings page.","ARMember"). '</span>';
                            $paid_post_html .= '</div>';
                        $paid_post_html .= '</div>';


                    $paid_post_html .= '</div>';

                    $arm_show_ppost_alt_content_wrapper = ' hidden_section ';

                    if( $enable_ppost_alternate_content ){
                        $arm_show_ppost_alt_content_wrapper = '';
                    }

                    $paid_post_html .= '<div class="arm_paid_post_row arm_paid_post_row_alternate_content_row ' . $arm_show_ppost_alt_content_wrapper . '">';

                        $paid_post_html .= '<div class="arm_paid_post_row_left arm_paid_post_row_float_left">' . esc_html__('Enter Alternative Content', 'ARMember') . '</div>';

                        $paid_post_html .= '<div class="arm_paid_post_row_right arm_paid_post_row_alternative_content_wrapper">';

                            $arm_alternate_arr_settings = array(
                                'media_buttons' => true,
                                'textarea_rows' => 15,
                                'default_editor' => 'html'
                            );

                            $arm_pp_alt_content_id = '';
                            if( isset( $_GET['action'] ) && 'edit_paid_post' == $_GET['action'] ) {
                                $arm_pp_alt_content_id = '-edit';
                            }

                            $post_alternative_content = get_post_meta( $post_id, 'arm_paid_post_alternative_content', true );

                            $arm_global_settings_general_settings_default_content = !empty($arm_global_settings->global_settings['arm_pay_per_post_default_content']) ? stripslashes($arm_global_settings->global_settings['arm_pay_per_post_default_content']) : __('Content is Restricted. Buy this post to get access to full content.', 'ARMember');

                            if( !empty( $arm_show_ppost_alt_content_wrapper ) && empty( $post_alternative_content ) ){
                                if( !empty( $arm_global_settings_general_settings_default_content ) ){
                                    $post_alternative_content = $arm_global_settings_general_settings_default_content;
                                }
                            } else if( empty( $post_alternative_content ) )
                            {
                                $post_alternative_content = $arm_global_settings_general_settings_default_content;
                            }

                            ob_start();

                            wp_editor(stripslashes_deep($post_alternative_content), 'arm_paid_post_alternative_content' . $arm_pp_alt_content_id, $arm_alternate_arr_settings);

                            $paid_post_alternate_content_editor = ob_get_clean();

                            $paid_post_html .= $paid_post_alternate_content_editor;

                        $paid_post_html .= '</div>';

                    $paid_post_html .= '</div>';

                $paid_post_html .= '</div>';

            $paid_post_html .= '</div>';

            $paid_post_html .= '<script>
                                jQuery(document).on("change", "#arm_enable_paid_post", function(e) {
                                    if(jQuery(this).is(":checked")) {
                                        jQuery("#arm_enable_paid_post_hidden").val(1);
                                        console.log("arm_enable_paid_post_hidden : " + arm_enable_paid_post_hidden);
                                    } else {
                                        jQuery("#arm_enable_paid_post_hidden").val(0);
                                        console.log("arm_enable_paid_post_hidden-2 : " + arm_enable_paid_post_hidden);
                                    }
                                    
                                });
                            </script>';

            if( $return ){
                return $paid_post_html;
            } else {
                echo $paid_post_html;
            }

        }

        function arm_display_shortcode_buttons_for_alternate_button( $post_type, $editor_id ){

            if( 'arm_paid_post_alternative_content' == $editor_id || 'arm_paid_post_alternative_content-edit' == $editor_id ){
                global $post;
                if( isset( $post ) && isset( $post->post_type ) ){
                    array_push( $post_type, $post->post_type );
                } else if( !empty($_GET['page']) && 'arm_manage_pay_per_post' == $_GET['page'] ){
                    array_push( $post_type, 'arm_pay_per_post' );
                } else if( !empty($_POST['action']) && 'edit_paid_post' == $_POST['action'] && 'arm_paid_post_alternative_content-edit' == $editor_id ){
                    array_push( $post_type, 'arm_pay_per_post' );
                }
            } else if( 'arm_pay_per_post_content' == $editor_id ){
                array_push( $post_type, 'arm_pay_per_post' );
            }

            array_unique( $post_type );

            return $post_type;
        }

        function arm_allowed_pages_for_media_buttons_buttons( $pages, $editor_id ){

            if( 'arm_pay_per_post_content' == $editor_id && isset( $_GET['page'] ) && 'arm_general_settings' == $_GET['page'] && isset( $_GET['action'] ) && 'pay_per_post_setting' == $_GET['action'] ){
                array_push( $pages, basename( $_SERVER['PHP_SELF'] ) );
            } else if( 'arm_paid_post_alternative_content' == $editor_id && isset( $_GET['page'] ) && 'arm_manage_pay_per_post' == $_GET['page'] ){
                array_push( $pages, basename( $_SERVER['PHP_SELF'] ) );
            } else if( 'arm_paid_post_alternative_content-edit' == $editor_id && isset( $_GET['action'] ) && 'edit_paid_post' == $_GET['action'] ){
                array_push( $pages, basename( $_SERVER['PHP_SELF'] ) );
            }

            return $pages;

        }

        function arm_allowed_post_type_for_external_editors_callback( $post_type, $editor_id ){

            if( 'arm_pay_per_post_content' == $editor_id && empty($post_type) ){
                $post_type = 'arm_pay_per_post';
            } else if( ( 'arm_paid_post_alternative_content' == $editor_id || 'arm_paid_post_alternative_content-edit' == $editor_id ) && empty($post_type) ){
                $post_type = 'arm_pay_per_post';
            }

            return $post_type;
        }

        function arm_allowed_pages_for_shortcode_popup_callback( $pages ){
            if( isset( $_GET['page'] ) && 'arm_general_settings' == $_GET['page'] && isset( $_GET['action'] ) && 'pay_per_post_setting' == $_GET['action'] ){
                array_push( $pages, basename( $_SERVER['PHP_SELF'] ) );
            } else if( isset( $_GET['page'] ) && 'arm_manage_pay_per_post' == $_GET['page'] && (!empty($_GET['action']) && in_array($_GET['action'], array("add_paid_post", "edit_paid_post")) ) ){
                array_push( $pages, basename( $_SERVER['PHP_SELF'] ) );
            }
            return $pages;

        }

        function arm_enqueue_shortcode_styles_callback( $pages ){
            
            if( isset( $_GET['page'] ) && 'arm_general_settings' == $_GET['page'] && isset( $_GET['action'] ) && 'pay_per_post_setting' == $_GET['action'] ){
                array_push( $pages, basename( $_SERVER['PHP_SELF'] ) );
            } else if( isset( $_GET['page'] ) && 'arm_manage_pay_per_post' == $_GET['page'] ){
                array_push( $pages, basename( $_SERVER['PHP_SELF'] ) );
            }
            return $pages;

        }

        function arm_paid_post_shortcode_add_tab_buttons($tab_buttons =array()){
            $tab_buttons =' <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_paid_post arm_hidden">
                                    <div class="popup_content_btn_wrapper">
                                            <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_paid_post" data-code="arm_user_paid_post">'.esc_html__('Add Shortcode', 'ARMember').'</button>
                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)">'.esc_html__('Cancel', 'ARMember').'</a>
                                    </div>
                            </div>';
            echo $tab_buttons;
        }

        function arm_plugin_install() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_pay_per_post'], '1');
            if (empty($_POST['slug'])) {
                wp_send_json_error(array(
                    'slug' => '',
                    'errorCode' => 'no_plugin_specified',
                    'errorMessage' => esc_html__('No plugin specified.', 'ARMember'),
                ));
            }

            $status = array(
                'install' => 'plugin',
                'slug' => sanitize_key(wp_unslash($_POST['slug'])),
            );

            if (!current_user_can('install_plugins')) {
                $status['errorMessage'] = esc_html__('Sorry, you are not allowed to install plugins on this site.', 'ARMember');
                wp_send_json_error($status);
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php')) {
                include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/plugin-install.php'))
                include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

            $api = plugins_api('plugin_information', array(
                'slug' => sanitize_key(wp_unslash($_POST['slug'])),
                'fields' => array(
                    'sections' => false,
                ),
            ));

            if (is_wp_error($api)) {
                $status['errorMessage'] = $api->get_error_message();
                wp_send_json_error($status);
            }

            $status['pluginName'] = $api->name;

            $skin = new WP_Ajax_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader($skin);

            $result = $upgrader->install($api->download_link);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                $status['debug'] = $skin->get_upgrade_messages();
            }

            if (is_wp_error($result)) {
                $status['errorCode'] = $result->get_error_code();
                $status['errorMessage'] = $result->get_error_message();
                wp_send_json_error($status);
            } elseif (is_wp_error($skin->result)) {
                $status['errorCode'] = $skin->result->get_error_code();
                $status['errorMessage'] = $skin->result->get_error_message();
                wp_send_json_error($status);
            } elseif ($skin->get_errors()->get_error_code()) {
                $status['errorMessage'] = $skin->get_error_messages();
                wp_send_json_error($status);
            } elseif (is_null($result)) {
                global $wp_filesystem;

                $status['errorCode'] = 'unable_to_connect_to_filesystem';
                $status['errorMessage'] = esc_html__('Unable to connect to the filesystem. Please confirm your credentials.', 'ARMember');

                if ($wp_filesystem instanceof WP_Filesystem_Base && is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
                    $status['errorMessage'] = esc_html($wp_filesystem->errors->get_error_message());
                }

                wp_send_json_error($status);
            }
            $install_status = $this->arm_install_plugin_install_status($api);


            if (current_user_can('activate_plugins') && is_plugin_inactive($install_status['file'])) {
                $status['activateUrl'] = add_query_arg(array(
                    '_wpnonce' => wp_create_nonce('activate-plugin_' . $install_status['file']),
                    'action' => 'activate',
                    'plugin' => $install_status['file'],
                        ), network_admin_url('plugins.php'));
            }

            if (is_multisite() && current_user_can('manage_network_plugins')) {
                $status['activateUrl'] = add_query_arg(array('networkwide' => 1), $status['activateUrl']);
            }
            $status['pluginFile'] = $install_status['file'];

            wp_send_json_success($status);
        }


        function arm_activate_plugin() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_pay_per_post'], '1');
            $plugin = $_POST['slug'];
            $plugin = plugin_basename(trim($plugin));
            $network_wide = false;
            $silent = false;
            $redirect = '';
            if (is_multisite() && ( $network_wide || is_network_only_plugin($plugin) )) {
                $network_wide = true;
                $current = get_site_option('active_sitewide_plugins', array());
                $_GET['networkwide'] = 1;
            } else {
                $current = get_option('active_plugins', array());
            }

            $valid = validate_plugin($plugin);
            if (is_wp_error($valid))
                return $valid;

            if (( $network_wide && !isset($current[$plugin]) ) || (!$network_wide && !in_array($plugin, $current) )) {
                if (!empty($redirect))
                    wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect));
                ob_start();
                wp_register_plugin_realpath(WP_PLUGIN_DIR . '/' . $plugin);
                $_wp_plugin_file = $plugin;
                include_once( WP_PLUGIN_DIR . '/' . $plugin );
                $plugin = $_wp_plugin_file; 

                if (!$silent) {
                    do_action('activate_plugin', $plugin, $network_wide);
                    do_action('activate_' . $plugin, $network_wide);
                }

                if ($network_wide) {
                    $current = get_site_option('active_sitewide_plugins', array());
                    $current[$plugin] = time();
                    update_site_option('active_sitewide_plugins', $current);
                } else {
                    $current = get_option('active_plugins', array());
                    $current[] = $plugin;
                    sort($current);
                    update_option('active_plugins', $current);
                }

                if (!$silent) {
                    do_action('activated_plugin', $plugin, $network_wide);
                }
                $response = array();
                if (ob_get_length() > 0) {
                    $response = array(
                        'type' => 'error'
                    );
                    echo json_encode($response);
                    die();
                } else {
                    $response = array(
                        'type' => 'success'
                    );
                    echo json_encode($response);
                    die();
                }
            }
        }

        function arm_deactivate_plugin() {
            global $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_pay_per_post'], '1');
            $plugin = $_POST['slug'];
            $silent = false;
            $network_wide = false;
            if (is_multisite())
                $network_current = get_site_option('active_sitewide_plugins', array());
            $current = get_option('active_plugins', array());
            $do_blog = $do_network = false;


            $plugin = plugin_basename(trim($plugin));


            $network_deactivating = false !== $network_wide && is_plugin_active_for_network($plugin);

            if (!$silent) {
                do_action('deactivate_plugin', $plugin, $network_deactivating);
            }

            if (false != $network_wide) {
                if (is_plugin_active_for_network($plugin)) {
                    $do_network = true;
                    unset($network_current[$plugin]);
                } elseif ($network_wide) {
                    
                }
            }

            if (true != $network_wide) {
                $key = array_search($plugin, $current);
                if (false !== $key) {
                    $do_blog = true;
                    unset($current[$key]);
                }
            }

            if (!$silent) {
                do_action('deactivate_' . $plugin, $network_deactivating);
                do_action('deactivated_plugin', $plugin, $network_deactivating);
            }


            if ($do_blog)
                update_option('active_plugins', $current);
            if ($do_network)
                update_site_option('active_sitewide_plugins', $network_current);

            $response = array(
                'type' => 'success'
            );
            echo json_encode($response);
            die();
        }
        // For manage page retrive paid post data 
        function arm_retrieve_paid_post_data(){

            global $wpdb, $ARMember, $arm_global_settings, $arm_slugs;
           
            $offset = isset( $_POST['iDisplayStart'] ) ? $_POST['iDisplayStart'] : 0;
            $limit = isset( $_POST['iDisplayLength'] ) ? $_POST['iDisplayLength'] : 10;

            $search_term = ( isset( $_POST['sSearch'] ) && '' != $_POST['sSearch'] ) ? true : false;

            $search_query = '';
            if( $search_term ){
                $search_query = "AND (arm_subscription_plan_name LIKE '%".$_POST['sSearch']."%' )";
            }

            $sortOrder = isset( $_POST['sSortDir_0'] ) ? $_POST['sSortDir_0'] : 'DESC';


            $orderBy = 'ORDER BY  arm_subscription_plan_post_id ' . $sortOrder;
            if( !empty( $_POST['iSortCol_0'] ) ){
                if( $_POST['iSortCol_0'] == 0 ){
                    $orderBy = 'ORDER BY arm_subscription_plan_post_id ' . $sortOrder;
                }else if($_POST['iSortCol_0'] == 1){
                    $orderBy = 'ORDER BY arm_subscription_plan_name ' . $sortOrder;
                }
            }

            $post_query = "SELECT * FROM {$ARMember->tbl_arm_subscription_plans} WHERE arm_subscription_plan_post_id != 0 AND arm_subscription_plan_is_delete = 0 {$search_query} {$orderBy}  LIMIT {$offset}, {$limit}";

            $get_posts = $wpdb->get_results( $post_query );

            $totalPosts_query =  "SELECT COUNT(arm_subscription_plan_post_id) AS total FROM {$ARMember->tbl_arm_subscription_plans} WHERE arm_subscription_plan_post_id != 0 AND arm_subscription_plan_is_delete = 0  {$orderBy}";

            $totalPosts_result = $wpdb->get_results( $totalPosts_query );
            $totalPosts = $totalPosts_result[0]->total;
                                              
            $grid_data = array();
            $ai = 0;
            if( !empty( $get_posts )){
                foreach ($get_posts as $key => $post) {
                    if( !isset($grid_data[$ai]) || !is_array( $grid_data[$ai] ) ){
                        $grid_data[$ai] = array();
                    }

                    $planObj = new ARM_Plan();
                    $planObj->init((object) $post);
                    $arm_subscription_plan_post_id = $post->arm_subscription_plan_post_id;

                    $total_users = $this->arm_get_total_members_in_paid_post($arm_subscription_plan_post_id);
                    
                    $edit_link = admin_url('admin.php?page=arm_manage_pay_per_post&action=edit_paid_post&post_id='.$arm_subscription_plan_post_id);
                    $grid_data[$ai][] =  '<a href="'.$edit_link.'">'.$arm_subscription_plan_post_id.'</a>';
                    $grid_data[$ai][] =  '<a href="'.$edit_link.'">'.$post->arm_subscription_plan_name.'</a>';

                    if( $planObj->is_recurring() && isset($planObj->options['payment_cycles']) && count($planObj->options['payment_cycles']) > 1 ) {
                        $duration =  '<span class="arm_item_status_text active">' . __('Paid', 'ARMember') . '</span><br/>
                        <a href="javascript:void(0);" onclick="arm_paid_post_cycle('. $arm_subscription_plan_post_id .')">' . __('Multiple Cycle', 'ARMember') . '</a>';
                    } else {
                        $duration = $planObj->plan_text(true);
                    }
                    
                    $grid_data[$ai][] = $duration;


                    $planMembers = $total_users;
                    
                    $grid_data[$ai][] = $planMembers;                                   

                    $gridAction = "<div class='arm_grid_action_btn_container'>";
                    
                        $gridAction .= "<a class='arm_paid_post_members_list_detail' href='javascript:void(0);' data-list_id='{$arm_subscription_plan_post_id}' data-list_type='drip' data-paid-post-name='{$post->arm_subscription_plan_name}'><img src='".MEMBERSHIP_IMAGES_URL."/grid_preview.png' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_preview_hover.png';\" class='armhelptip' title='".__('View Members','ARMember')."' onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_preview.png';\" /></a>";
                        
                        $gridAction .= "<a class='arm_edit_paid_post_btn' href='".admin_url('admin.php?page=arm_manage_pay_per_post&action=edit_paid_post&post_id=' . $arm_subscription_plan_post_id)."' data-post_id='{$arm_subscription_plan_post_id}'><img src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit_hover.png';\" class='armhelptip' title='".__('Edit Paid Post','ARMember')."' onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png';\" /></a>";

                        $gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$arm_subscription_plan_post_id});'><img src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png' class='armhelptip' title='".esc_html__('Delete','ARMember')."' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete_hover.png';\" onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png';\" /></a>";
                        $gridAction .= $arm_global_settings->arm_get_confirm_box($post->arm_subscription_plan_post_id, esc_html__("Are you sure you want to delete the Paid Post?", 'ARMember'), 'arm_paid_post_delete_btn');
                    
                    $gridAction .= "</div>";

                    $grid_data[$ai][] = '<div class="arm_grid_action_wrapper">'.$gridAction.'</div>';

                    $ai++;
                }
            }

            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10);
            $after_filter = $totalPosts;
            if( $search_term ){
                $after_filter = $ai;
            }

            $response = array(
                'sColumns' => implode(',',array('Post ID','Post Title','Post Type','Paid Post Members')),
                'sEcho' => $sEcho,
                'iTotalRecords' => $totalPosts,
                'iTotalDisplayRecords' => $after_filter,
                'aaData' => $grid_data
            );

            echo json_encode( $response );
            die;

        }
        // For manage page retrive paid post members data 
        function arm_get_paid_post_members_data_func() {
            global $wpdb,$ARMember, $arm_capabilities_global,$arm_slugs;
            
            $postID = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0;
            $response = array('status' => 'error', 'data' => array());
            if(0 != $postID) {
                $membersDatasDefault = array();
                $response['status'] = "success";
                $response['data'] = $membersDatasDefault;

                $arm_post_query = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$ARMember->tbl_arm_subscription_plans}` WHERE `arm_subscription_plan_post_id` = %d",$postID));
                
                $arm_user_query = $wpdb->get_results($wpdb->prepare("SELECT `user_id`, `meta_value` FROM `".$wpdb->usermeta."` WHERE `meta_key` = %s",'arm_user_plan_ids'));

                $arm_user_array = array(); 
                if(!empty($arm_user_query)){
                     foreach($arm_user_query as $arm_user){
                         $user_meta=get_userdata($arm_user->user_id);
                         $user_roles=$user_meta->roles;
                         if(!in_array('administrator', $user_roles)) {
                             $arm_user_array[$arm_user->user_id] = maybe_unserialize($arm_user->meta_value);
                         }
                     }
                 }

                    if(!empty($arm_post_query)){
                        foreach ($arm_post_query as $arm_post_key => $arm_post_id) {

                            $planObj = new ARM_Plan();
                            $planObj->init((object) $arm_post_id);
                            $planID = $arm_post_id->arm_subscription_plan_id;

                            $total_users = 0;
                            if(!empty($arm_user_array)){

                                $membersData = array();
                                 
                                foreach($arm_user_array as $arm_user_id => $arm_user_plans){

                                    if(in_array($planID, $arm_user_plans)){
                                       
                                       $membersDatas = array();
                                       $user_data = get_user_by('ID',$arm_user_id);
                                        
                                       $membersDatas['username'] = $user_data->user_login;
                                       $membersDatas['user_email'] = $user_data->user_email;
                                       $view_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $user_data->ID);
                                       $membersDatas['view_detail'] = "<center><a class='arm_openpreview' href='{$view_link}'>" . __('View Detail', 'ARMember') . "</a></center>";
                                       $membersData[] = array_values($membersDatas); 
                                    }
                                }
                                
                            }
                        }
                        $response['status'] = "success";
                        $response['data'] = $membersData;
                    }
            }
            echo json_encode($response);
            die;
        }

        function get_arm_paid_post_plan_list_func(){
            
            if(isset($_REQUEST['action']) && $_REQUEST['action']=='get_arm_paid_post_plan_list') {
                
                $text = $_REQUEST['txt'];
                $type = 0;
                global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;

                $arm_subscription_plans_table = $ARMember->tbl_arm_subscription_plans;
                
               $paid_post_where = " WHERE ";
               $paid_post_where .= "(`arm_subscription_plan_name` LIKE '".$text."%')";

                $operator = " AND ";

                $paid_post_where .= " {$operator} `arm_subscription_plan_status`='1' AND `arm_subscription_plan_is_delete`='0' AND `arm_subscription_plan_post_id`!='0' ";
                $paid_post_fields = "arm_subscription_plan_name,arm_subscription_plan_description,arm_subscription_plan_id,arm_subscription_plan_post_id";
                $paid_post_order_by = " ORDER BY arm_subscription_plan_id DESC limit 0,10";
                
                $paid_post_query = "SELECT {$paid_post_fields} FROM `{$arm_subscription_plans_table}` {$paid_post_where} {$paid_post_order_by} ";
                $paid_post_plan_details = $wpdb->get_results($paid_post_query);

                $all_paid_post_plans = $paid_post_plan_details;
                
                $ppData = array();
                if(!empty($all_paid_post_plans)) {
                    foreach ( $all_paid_post_plans as $paid_post_plan ) {
                        
                        $paid_post_plan_list_html .= '<li data-id="'.$paid_post_plan->arm_subscription_plan_id.'">' . $paid_post_plan->arm_subscription_plan_name . '</li>';
                        $ppData[] = array(
                                    'id' => $paid_post_plan->arm_subscription_plan_id,
                                    'value' => $paid_post_plan->arm_subscription_plan_name,
                                    'label' => $paid_post_plan->arm_subscription_plan_name,
                                    'arm_paid_post_id' => $paid_post_plan->arm_subscription_plan_post_id
                                );
                    }
                }
                $response = array('status' => 'success', 'data' => $ppData);
                echo json_encode($response);
                die;
            }    
        }

        function arm_get_paid_post_item_options(){
            global $wpdb, $ARMember;

            $arm_post_type = isset( $_POST['arm_post_type'] ) ? $_POST['arm_post_type'] : 'page';

            $search_key = isset( $_POST['search_key'] ) ? $_POST['search_key'] : '';

            if( $search_key != '' ){
                $postQuery = $wpdb->get_results( $wpdb->prepare( "SELECT p.ID, p.post_title FROM {$wpdb->posts} p WHERE p.post_type = %s AND p.post_title LIKE %s AND p.post_status = %s LIMIT 0,10", $arm_post_type, '%' . $wpdb->esc_like( $search_key ) . '%', 'publish' ) );
            } else {
                $postQuery = $wpdb->get_results( $wpdb->prepare( "SELECT p.ID, p.post_title FROM {$wpdb->posts} p WHERE p.post_type = %s AND p.post_status = %s LIMIT 0,10", $arm_post_type, 'publish' ) );
            }

            $ppData = array();
            if( isset( $postQuery ) && !empty( $postQuery ) ){
                foreach( $postQuery as $k => $postData ){
                    $isEnablePaidPost = get_post_meta( $postData->ID, 'arm_is_paid_post', true );
                    if( 0 == $isEnablePaidPost || empty($isEnablePaidPost) ){
                        $ppData[] = array(
                            'id' => $postData->ID,
                            'value' => $postData->post_title,
                            'label' => $postData->post_title
                        );
                    }
                }
            }

            $response = array('status' => 'success', 'data' => $ppData);
            echo json_encode($response);
            die;
        }

        function arm_update_paid_post_status() {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $postmeta_table = $wpdb->postmeta;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_pay_per_post'], '1');
            $response = array('type' => 'error', 'msg' => __('Sorry, Something went wrong. Please try again.', 'ARMember'));
            if (!empty($_POST['paid_post_id']) && $_POST['paid_post_id'] != 0) {
                $paid_post_id = intval($_POST['paid_post_id']);
                $paid_post_id_status = (!empty($_POST['paid_post_id_status'])) ? intval($_POST['paid_post_id_status']) : 0;
                $wpdb->update($postmeta_table, array('arm_paid_post_status' => $paid_post_id), array('arm_post_id' => $paid_post_id_status));
                $response = array('type' => 'success', 'msg' => __('Paid Post Updated Successfully.', 'ARMember'));
            }
            echo json_encode($response);
            die();
        }

        function arm_delete_single_paid_post() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_pay_per_post'], '1');
            $action = $_POST['act'];
            $post_id = intval($_POST['id']);
            if ($action == 'delete') {
                if (empty($post_id)) {
                    $errors[] = __('Invalid action.', 'ARMember');
                } else {
                    update_post_meta( $post_id, 'arm_is_paid_post', 0 );

                    $is_post_exists = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d", $post_id ) );

                    if( '' != $is_post_exists->arm_subscription_plan_id ){

                        delete_post_meta( $post_id, 'arm_access_plan', $is_post_exists->arm_subscription_plan_id );

                        $wpdb->update(
                            $ARMember->tbl_arm_subscription_plans,
                            array(
                                'arm_subscription_plan_is_delete' => 1
                            ),
                            array(
                                'arm_subscription_plan_id' => $is_post_exists->arm_subscription_plan_id
                            )
                        );
                    }
                    $message[] = esc_html__('Paid Post removed successfully', 'ARMember');
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }
        function arm_edit_paid_post_data() {
            global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_pay_per_post'], '1');
            $return = array('status' => 'success');

            $post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';

            if( empty($post_id) ){
                $return['status'] = 'error';
            } else {
                $post_obj = get_post( $post_id );
                $msg = $this->arm_add_paid_post_metabox_html( $post_obj, array(), true, true );
                $return['message'] = $msg;
            }
            
            echo json_encode($return);
            exit;
        }


        /**
         * Get all posts
         * @return array of posts, False if there is no post(s).
         */
        function arm_get_all_subscription_posts($fields = 'all', $object_type = ARRAY_A, $allow_user_no_post = false) {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $selectFields = '*';
            if (!empty($fields)) {
                if ($fields != 'all' && $fields != '*') {
                    $selectFields = $fields;
                }
            }
            $object_type = !empty($object_type) ? $object_type : ARRAY_A;
            $results = $wpdb->get_results("SELECT {$selectFields}, `arm_subscription_plan_id` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`='0' AND `arm_subscription_plan_post_id`!='0' ORDER BY `arm_subscription_plan_id` DESC", $object_type);
            if (!empty($results) || $allow_user_no_post) {
                $posts_data = array();
                if ($allow_user_no_post) {
                    $plnID = -2;
                    $plnName = __('Users Having No Post', 'ARMember');
                    if ($object_type == OBJECT || $object_type == OBJECT_K) {
                        $sp->arm_subscription_plan_id = $plnID;
                        $sp->arm_subscription_plan_name = $plnName;
                        $sp->arm_subscription_plan_description = '';
                        $sp->arm_subscription_plan_options = array();
                    } else {
                        $sp['arm_subscription_plan_id'] = $plnID;
                        $sp['arm_subscription_plan_name'] = $plnName;
                        $sp['arm_subscription_plan_description'] = '';
                        $sp['arm_subscription_plan_options'] = array();
                    }
                    $posts_data[$plnID] = $sp;
                }
                if (!empty($results)) {
                    foreach ($results as $sp) {
                        if ($object_type == OBJECT || $object_type == OBJECT_K) {
                            $plnID = $sp->arm_subscription_plan_id;
                            if (isset($sp->arm_subscription_plan_name)) {
                                $sp->arm_subscription_plan_name = stripslashes($sp->arm_subscription_plan_name);
                            }
                            if (isset($sp->arm_subscription_plan_description)) {
                                $sp->arm_subscription_plan_description = stripslashes($sp->arm_subscription_plan_description);
                            }
                            if (isset($sp->arm_subscription_plan_options)) {
                                $sp->arm_subscription_plan_options = maybe_unserialize($sp->arm_subscription_plan_options);
                            }
                        } else {
                            $plnID = $sp['arm_subscription_plan_id'];
                            if (isset($sp['arm_subscription_plan_name'])) {
                                $sp['arm_subscription_plan_name'] = stripslashes($sp['arm_subscription_plan_name']);
                            }
                            if (isset($sp['arm_subscription_plan_description'])) {
                                $sp['arm_subscription_plan_description'] = stripslashes($sp['arm_subscription_plan_description']);
                            }
                            if (isset($sp['arm_subscription_plan_options'])) {
                                $sp['arm_subscription_plan_options'] = maybe_unserialize($sp['arm_subscription_plan_options']);
                            }
                        }
                        $posts_data[$plnID] = $sp;
                    }
                }
                return $posts_data;
            } else {
                return FALSE;
            }
        }

        function arm_get_all_active_subscription_posts($orderby = '', $order = '', $allow_user_no_post = false) {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $orderby = (!empty($orderby)) ? $orderby : 'arm_subscription_plan_id';
            $order = (!empty($order) && $order == 'ASC') ? 'ASC' : 'DESC';
            /* Query Monitor Settings */
            if( isset($GLOBALS['arm_active_subscription_post_data'])){
                $results = $GLOBALS['arm_active_subscription_post_data'];
            } else {
                $results = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_status`='1' AND `arm_subscription_plan_is_delete`='0' AND `arm_subscription_plan_post_id`!='0' ORDER BY `" . $orderby . "` " . $order . "", ARRAY_A);

                $GLOBALS['arm_active_subscription_post_data'] = $results;
            }
            if (!empty($results) || $allow_user_no_post) {
                $posts_data = array();
                if ($allow_user_no_post) {
                    $sp['arm_subscription_plan_id'] = -2;
                    $sp['arm_subscription_plan_name'] = __('Users Having No Plan', 'ARMember');
                    $sp['arm_subscription_plan_description'] = '';
                    $sp['arm_subscription_plan_options'] = array();
                    $posts_data[$sp['arm_subscription_plan_id']] = $sp;
                }
                if (!empty($results)) {
                    foreach ($results as $sp) {
                        $sp['arm_subscription_plan_name'] = stripslashes($sp['arm_subscription_plan_name']);
                        $sp['arm_subscription_plan_description'] = stripslashes($sp['arm_subscription_plan_description']);
                        $sp['arm_subscription_plan_options'] = maybe_unserialize($sp['arm_subscription_plan_options']);
                        $posts_data[$sp['arm_subscription_plan_id']] = $sp;
                    }
                }
                return $posts_data;
            } else {
                return FALSE;
            }
        }

        function arm_get_total_active_post_counts() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $post_counts = $wpdb->get_var("SELECT COUNT(`arm_subscription_plan_id`) FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_status`='1' AND `arm_subscription_plan_is_delete`='0' AND `arm_subscription_plan_post_id`!='0'");
            return $post_counts;
        }

        function arm_get_total_post_counts() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $post_counts = $wpdb->get_var("SELECT COUNT(`arm_subscription_plan_id`) FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`='0' AND `arm_subscription_plan_post_id`!='0'");
            return $post_counts;
        }

        function arm_update_user_paid_post_ids( $null, $obj_id, $meta_key, $meta_value, $prev_value ){

            if( 'arm_user_plan_ids' == $meta_key ){

                $meta_value_arr = maybe_unserialize( $meta_value );

                $this->arm_update_user_post_ids( $obj_id, $meta_value_arr );

            }

            return $null;

        }
	
	    function arm_update_user_post_ids($user_id, $plan_id){
            global $wp, $wpdb, $ARMember;
            
            if($this->isPayPerPostFeature==true){

                $post_ids = $this->arm_get_post_from_plan_id( $plan_id );
                
                //$arm_post_meta_data = get_user_meta($user_id, 'arm_user_post_ids', true);

                //if(empty($arm_post_meta_data)){
                    $arm_post_meta_data = array();
                //}

                if( !empty( $post_ids ) ){
                    foreach( $post_ids as $post_id ){
                        if( !empty($post_id['arm_subscription_plan_post_id']) ){
                            $arm_post_meta_data[$post_id['arm_subscription_plan_id']] =  $post_id['arm_subscription_plan_post_id'];
                        }
                    }
                }
                update_user_meta($user_id, 'arm_user_post_ids', $arm_post_meta_data);
            }
        }

        function arm_add_paid_post_plan_in_setup_data( $setup_data, $posted_data ){

            if( isset( $posted_data['arm_paid_post'] ) && '' != $posted_data['arm_paid_post'] ){

                $setup_modules = $setup_data['arm_setup_modules']['modules'];
                $setup_modules2 = $setup_data['setup_modules']['modules'];

                if( !isset( $setup_modules['plans'] ) ){
                    $setup_data['arm_setup_modules']['modules']['plans']  = array( $posted_data['arm_paid_post'] );
                } else if( isset( $setup_modules['plans'] ) && !in_array( $posted_data['arm_paid_post'], $setup_modules['plans'] ) ){
                    $setup_data['arm_setup_modules']['modules']['plans'][] = $posted_data['arm_paid_post'];
                }

                if( !isset( $setup_modules2['plans'] ) ){
                    $setup_data['setup_modules']['modules']['plans']  = array( $posted_data['arm_paid_post'] );
                } else if( isset( $setup_modules2['plans'] ) && !in_array( $posted_data['arm_paid_post'], $setup_modules2['plans'] ) ){
                    $setup_data['setup_modules']['modules']['plans'][] = $posted_data['arm_paid_post'];
                }
            }

            return $setup_data;

        }
	
        function arm_modify_setup_data_for_paid_post_type_setup( $setup_data, $args ){
            global $arm_global_settings;
            $all_global_settings = $arm_global_settings;
            $general_settings = isset($all_global_settings->global_settings) ? $all_global_settings->global_settings : array();

            $arm_pay_per_post_buynow_var = (!empty($general_settings['arm_pay_per_post_buynow_var'])) ? $general_settings['arm_pay_per_post_buynow_var'] : 'arm_paid_post';

            $setup_type = isset( $setup_data['arm_setup_type'] ) ? $setup_data['arm_setup_type'] : 0;

            if( 1 == $setup_type ){
                $setup_data['arm_setup_modules']['modules']['plans'] = !empty($setup_data['arm_setup_modules']['modules']['plans']) ? $setup_data['arm_setup_modules']['modules']['plans'] : array();
                $setup_data['setup_modules']['modules']['plans'] = !empty($setup_data['setup_modules']['modules']['plans']) ? $setup_data['setup_modules']['modules']['plans'] : array();
                $setup_data['arm_setup_modules']['modules']['plans'] = $this->arm_remove_non_paid_post_plan( $setup_data['arm_setup_modules']['modules']['plans'] );
                $setup_data['setup_modules']['modules']['plans'] = $this->arm_remove_non_paid_post_plan( $setup_data['setup_modules']['modules']['plans'] );
            }

            $paid_post_id = "";

            if( !isset( $_GET[$arm_pay_per_post_buynow_var] ) ) {
                if(function_exists('get_the_ID')){
                	$paid_post_id = get_the_ID();
                }
                if( !empty( $args['is_arm_paid_post'] ) && 1 == $args['is_arm_paid_post'] ){
                    $paid_post_id = $args['paid_post_id'];
                }
                if(empty($paid_post_id)){
                	return $setup_data;
                }
            } else {
                $paid_post_id = isset( $_GET[$arm_pay_per_post_buynow_var] ) ? $_GET[$arm_pay_per_post_buynow_var] : '';
            }

            if( empty($paid_post_id) ){
                return $setup_data;
            }


            if( !isset( $setup_type ) || ( isset( $setup_type ) && 1 != $setup_type ) ){
                return $setup_data;
            }

            $plan_id = $this->arm_get_plan_from_post_id( $paid_post_id );

            if( isset( $plan_id ) && '' != $plan_id ){

                if( !isset( $setup_data['arm_setup_modules']['modules']['plans'] ) ){
                    $setup_data['arm_setup_modules']['modules']['plans'] = array( $plan_id );
                } else {
                    $setup_data['arm_setup_modules']['modules']['plans'][] = $plan_id;
                }

                $plan_order = isset( $setup_data['arm_setup_modules']['modules']['plans_order'] ) ? $setup_data['arm_setup_modules']['modules']['plans_order'] : array();

                if( empty( $plan_order ) ){
                    $setup_data['arm_setup_modules']['modules']['plans_order'][$plan_id] = 1;
                } else {
                    $maxOrder = max( $plan_order );
                    $nextOrder = $maxOrder + 1;
                    $setup_data['arm_setup_modules']['modules']['plans_order'][$plan_id] = $nextOrder;
                }

                if( !isset( $setup_data['setup_modules']['modules']['plans'] ) ){
                    $setup_data['setup_modules']['modules']['plans'] = array( $plan_id );
                } else {
                    $setup_data['setup_modules']['modules']['plans'][] = $plan_id;
                }

                $plan_order2 = isset( $setup_data['setup_modules']['modules']['plans_order'] ) ? $setup_data['setup_modules']['modules']['plans_order'] : array();

                if( empty( $plan_order2 ) ){
                    $setup_data['setup_modules']['modules']['plans_order'][$plan_id] = 1;
                } else {
                    $maxOrder = max( $plan_order2 );
                    $nextOrder = $maxOrder + 1;
                    $setup_data['setup_modules']['modules']['plans_order'][$plan_id] = $nextOrder;
                }

                $setup_data['arm_paid_post_plan_id'] = $plan_id;

            }


            if(!current_user_can('administrator'))
            {
                global $wpdb, $ARMember;

                $planType = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_type FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE arm_subscription_plan_id = %d AND arm_subscription_plan_is_delete = %d", $plan_id, 0 ) );

                if(is_user_logged_in() && (!empty($planType->arm_subscription_plan_type)) && $planType->arm_subscription_plan_type == "free" )
                {
                    $login_user_id = get_current_user_id();
                    $arm_paid_plan_ids = get_user_meta($login_user_id, 'arm_user_plan_ids', true);
                    if(!in_array($plan_id, $arm_paid_plan_ids))
                    {
                        do_action( 'arm_apply_plan_to_member', $plan_id, $login_user_id);
                        $paid_post_redirect = get_permalink($paid_post_id);
                        wp_redirect($paid_post_redirect);
                    }
                }
            }

            return $setup_data;
        }

        function arm_get_plan_from_post_id( $post_id = '' ){
            if( empty($post_id) ){
                return;
            }

            global $wpdb, $ARMember;

            $planId = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE arm_subscription_plan_post_id = %d AND arm_subscription_plan_is_delete = %d", $post_id, 0 ) );

            if( isset( $planId->arm_subscription_plan_id ) ){
                return $planId->arm_subscription_plan_id;
            } else {
                return '';
            }

        }

        function arm_get_post_from_plan_id( $plan_id ){
            if( empty($plan_id) ){
                return;
            }

            global $wpdb, $ARMember;

            if( is_array( $plan_id ) && !empty($plan_id) ){
                $postId = $wpdb->get_results( "SELECT arm_subscription_plan_id,arm_subscription_plan_post_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE arm_subscription_plan_id IN (". implode( ',', $plan_id ) .")", ARRAY_A );
            } else {
                $postId = $wpdb->get_results( $wpdb->prepare( "SELECT arm_subscription_plan_id,arm_subscription_plan_post_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE arm_subscription_plan_id = %d", $plan_id ), ARRAY_A );
            }

            return $postId;            
        }

        function arm_add_paid_post_plan_in_active_subscription_pans( $all_active_plans ){
            $paid_post_id = "";
	    global $arm_global_settings;
            $all_global_settings = $arm_global_settings;
            $general_settings = isset($all_global_settings->global_settings) ? $all_global_settings->global_settings : array();

            $arm_pay_per_post_buynow_var = (!empty($general_settings['arm_pay_per_post_buynow_var'])) ? $general_settings['arm_pay_per_post_buynow_var'] : 'arm_paid_post';
            if( !isset( $_REQUEST[$arm_pay_per_post_buynow_var] ) ) {
                if(function_exists('get_the_ID'))
		{
                	$paid_post_id = get_the_ID();
		}
		if(empty($paid_post_id))
		{
                	return $all_active_plans;
		}
            }else{
                $paid_post_id = isset( $_REQUEST[$arm_pay_per_post_buynow_var] ) ? $_REQUEST[$arm_pay_per_post_buynow_var] : '';
            }


            if( empty($paid_post_id) ){
                return $all_active_plans;
            }

            global $wpdb, $ARMember;

            $planId = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE arm_subscription_plan_post_id = %d AND arm_subscription_plan_is_delete = %d", $paid_post_id, 0 ), ARRAY_A );

            if( isset( $planId ) && !empty( $planId ) ){

                $plan_id = $planId['arm_subscription_plan_id'];

                $planId['arm_subscription_plan_name'] = stripslashes($planId['arm_subscription_plan_name']);
                
                $planId['arm_subscription_plan_description'] = stripslashes($planId['arm_subscription_plan_description']);
                
                $planId['arm_subscription_plan_options'] = maybe_unserialize($planId['arm_subscription_plan_options']);

                $all_active_plans[ $plan_id ] = $planId;

            }
        
            return $all_active_plans;
        }

        function arm_add_paid_post_plan_id( $module_content, $setupID, $setup_data ){


            if( isset( $setup_data['arm_paid_post_plan_id'] ) && '' != $setup_data['arm_paid_post_plan_id'] ){
                global $wpdb, $ARMember;

                $plan_id = $setup_data['arm_paid_post_plan_id'];

                $get_pp_id = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_post_id FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE arm_subscription_plan_id = %d", $plan_id ) );

                if( isset( $get_pp_id->arm_subscription_plan_post_id ) && '' != $get_pp_id->arm_subscription_plan_post_id ){

                    $module_content .= '<input type="hidden" name="arm_paid_post" value="'. $get_pp_id->arm_subscription_plan_post_id .'" />';

                }
                
            }

            return $module_content;
        }
	
	function arm_get_paid_post_setup(){
            global $wpdb, $ARMember;
            
            $getTotalPaidPostSetup = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(arm_setup_id) FROM `" . $ARMember->tbl_arm_membership_setup . "` WHERE `arm_setup_type` = %d", 1 ) );

            return $getTotalPaidPostSetup;
        }
	
	function arm_remove_non_paid_post_plan( $setup_plans = array() ){
            global $wpdb, $ARMember;

            if( empty( $setup_plans ) ){
                return $setup_plans;
            }

            $updated_setup_plans = array();
            foreach( $setup_plans as $plan_id ){
                $planData = new ARM_Plan( $plan_id );

                if( 0 < $planData->isPaidPost ){
                    array_push( $plan_id, $updated_setup_plans );
                }
            }

            return $updated_setup_plans;

        }

        function arm_update_paid_post_transaction( $payment_data ){
            global $wpdb, $ARMember;


            $plan_id = isset( $payment_data['arm_plan_id'] ) ? $payment_data['arm_plan_id'] : 0;

            if( !empty( $plan_id ) ){

                $planData = new ARM_Plan( $plan_id );

                if( !empty( $planData->isPaidPost ) ){
                    $wpdb->update(
                        $ARMember->tbl_arm_payment_log,
                        array(
                            'arm_is_post_payment' => 1,
                            'arm_paid_post_id' => $planData->isPaidPost
                        ),
                        array(
                            'arm_log_id' => $payment_data['arm_log_id']
                        )
                    );
                }
                
            }

        }


        /*
        function armpay_per_post_add_fancy_url_rule()
        {
            if($this->isPayPerPostFeature)
            {
                
                // if( get_option( 'armpay_per_post_flush_rewrites' ) ) {
                //     flush_rewrite_rules();
                //     delete_option( 'armpay_per_post_flush_rewrites' );
                // }


                global $arm_global_settings;
                $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                $general_settings = $all_global_settings['general_settings'];

                $arm_pay_per_post_referral_var = (!empty($general_settings['arm_pay_per_post_referral_var'])) ? $general_settings['arm_pay_per_post_referral_var'] : 'arm_paid_post_id';
                $arm_pay_per_post_allow_fancy_url = (!empty($general_settings['arm_pay_per_post_allow_fancy_url'])) ? $general_settings['arm_pay_per_post_allow_fancy_url'] : 0;

                
                // if($arm_pay_per_post_allow_fancy_url>0)
                // {
                //     $taxonomies = get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects' );
                //     foreach( $taxonomies as $tax_id => $tax ) {
                //         add_rewrite_rule( $tax->rewrite['slug'] . '/(.+?)/' . $arm_pay_per_post_referral_var . '(/(.*))?/?$', 'index.php?' . $tax_id . '=$matches[1]&' . $arm_pay_per_post_referral_var . '=$matches[3]', 'top');
                //     }
                //     //add_rewrite_rule( '/(.+?)/' . $arm_pay_per_post_referral_var . '(/(.*))?/?$', 'index.php?' . $arm_pay_per_post_referral_var . '=$matches[1]', 'top');
                //     add_rewrite_endpoint( $arm_pay_per_post_referral_var, EP_ALL );
                // }
                
            }
        }*/
	
	/**
        * `[arm_paid_post_buy_now]` shortcode function
        */
        function arm_paid_post_buy_now_func($atts, $content, $tag) {

            global $ARMember, $arm_global_settings;
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }

            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $atts = shortcode_atts(array(
                'label' => __('Buy Now', 'ARMember'),
                'type' => 'link',
                'redirect_url' => '',
                'success_url' => '',
                'link_css' => '',
                'link_hover_css' => '',
                    ), $atts, $tag);
            
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $arm_slugs;

            $paid_post_shortcode_redirect_url = !empty($atts['redirect_url']) ? $atts['redirect_url'] : '';
            $paid_post_shortcode_success_url = !empty($atts['success_url']) ? $atts['success_url'] : '';
            
            $arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();
            $page_settings = $arm_all_global_settings['page_settings'];
            $redirct_url = (!empty($page_settings['paid_post_page_id'])) ? $page_settings['paid_post_page_id'] : '';

            $redirect_to = "";
            $current_post_id = get_the_ID();
            $arm_post_hasaccess = false;
            if (!current_user_can('administrator') && is_singular() && in_the_loop() && is_main_query() ) 
            {
                
                if(!empty($current_post_id))
                {
                    $arm_is_paid_post = get_post_meta($current_post_id, 'arm_is_paid_post', true);
                    if(!empty($arm_is_paid_post))
                    {
                        $plan_id = $this->arm_get_plan_from_post_id( $current_post_id );
                        if( !empty( $plan_id ) )
                        {
                            
                            $isLoggedIn = is_user_logged_in();
                            if($isLoggedIn)
                            {
                                $current_user_id = get_current_user_id();
                                $arm_user_plan = get_user_meta($current_user_id, 'arm_user_plan_ids', true);
                                $arm_user_plan = !empty($arm_user_plan) ? $arm_user_plan : array();
                                if(!empty($arm_user_plan)){
                                    $suspended_plan_ids = get_user_meta($current_user_id, 'arm_user_suspended_plan_ids', true);
                                    if( ! empty($suspended_plan_ids)) {
                                        foreach ($suspended_plan_ids as $suspended_plan_id) {
                                            if(in_array($suspended_plan_id, $arm_user_plan)) {
                                                unset($arm_user_plan[array_search($suspended_plan_id, $arm_user_plan)]);
                                            }
                                        }
                                    }

                                    if(in_array($plan_id, $arm_user_plan))
                                    {
                                        $arm_post_hasaccess = true;
                                        $content='';
                                    }
                                }
                            }
                            
                        }
                        
                    }
                }
                
            }
            if($arm_post_hasaccess==false)
            {
           
                if(empty($paid_post_shortcode_redirect_url))
                {
                    if($redirct_url != "")
                    {
                        $redirect_to = get_the_permalink($page_settings['paid_post_page_id']);
                    }
                    else
                    {
                        $redirect_to = get_permalink($current_post_id);
                    }
                }
                else
                {
                    $redirect_to = $paid_post_shortcode_redirect_url;
                }


                $all_global_settings = $arm_global_settings;
                $general_settings = isset($all_global_settings->global_settings) ? $all_global_settings->global_settings : array();

                $arm_pay_per_post_buynow_var = (!empty($general_settings['arm_pay_per_post_buynow_var'])) ? $general_settings['arm_pay_per_post_buynow_var'] : 'arm_paid_post';
                $arm_pay_per_post_allow_fancy_url = (!empty($general_settings['arm_pay_per_post_allow_fancy_url'])) ? $general_settings['arm_pay_per_post_allow_fancy_url'] : '';
                $arm_pay_per_post_success_var = 'arm_success_url';

                
                if($redirect_to == "" && empty($current_post_id))
                {
                    $redirect_to = ARM_HOME_URL;
                }

                $arm_success_fancy_url = '';
                $query_arg = array();
                $query_arg[$arm_pay_per_post_buynow_var] = $current_post_id;
                if (!empty($paid_post_shortcode_success_url)) {
                    $arm_success_fancy_url = '/'.$arm_pay_per_post_success_var.'/'.$paid_post_shortcode_success_url;
                    $query_arg[$arm_pay_per_post_success_var] = $paid_post_shortcode_success_url;
                }

                $paid_post_buy_now_url = "";
                if(substr($redirect_to, -1) == '/')
                { 
                    $paid_post_buy_now_url = ($arm_pay_per_post_allow_fancy_url) ? $redirect_to.$arm_pay_per_post_buynow_var.'/'.$current_post_id.$arm_success_fancy_url : add_query_arg($query_arg, $redirect_to);
                }
                else
                {
                    $paid_post_buy_now_url = ($arm_pay_per_post_allow_fancy_url) ? $redirect_to."/".$arm_pay_per_post_buynow_var.'/'.$current_post_id.$arm_success_fancy_url : add_query_arg($query_arg, $redirect_to);
                }


                $paid_post_buy_now_url = wp_nonce_url($paid_post_buy_now_url);
                $paidPostWrapper = arm_generate_random_code();
                $content = apply_filters('arm_before_paid_post_buy_now_shortcode_content', $content, $atts);
                //$content .= '<div class="arm_paid_post_container" id="arm_paid_post_' . $paidPostWrapper . '">';
                $btnStyle = '';
                if (!empty($atts['link_css'])) {
                    $btnStyle .= '.arm_paid_post_buy_now_btn{' . esc_html($atts['link_css']) . '}';
                }
                if (!empty($atts['link_hover_css'])) {
                    $btnStyle .= '.arm_paid_post_buy_now_btn:hover{' . esc_html($atts['link_hover_css']) . '}';
                }
                if (!empty($btnStyle)) {
                    $content .= '<style type="text/css">' . $btnStyle . '</style>';
                }
                
                if ($atts['type'] == 'button') {
                    $content .= '<form method="post" class="arm_paid_post_buy_now" name="arm_paid_post_buy_now" action="' . $paid_post_buy_now_url . '" enctype="multipart/form-data">';
                    $content .= '<button type="submit" class="arm_paid_post_buy_now_btn arm_paid_post_buy_now_button">' . $atts['label'] . '</button>';
                    $content .= '</form>';
                } else {
                    $content .= '<a href="' . $paid_post_buy_now_url . '" title="' . $atts['label'] . '" class="arm_paid_post_buy_now_btn arm_paid_post_buy_now_link">' . $atts['label'] . '</a>';
                }
                    
                $content = apply_filters('arm_after_paid_post_buy_now_shortcode_content', $content, $atts);

                $ARMember->arm_check_font_awesome_icons($content);
            }
            return do_shortcode($content);
        }

    
        function arm_get_paid_post_plans_paging($user_id = 0, $current_page = 1, $per_page = 5){
            
            global $arm_global_settings,$arm_subscription_plans,$is_multiple_membership_feature;

            $arm_paid_post_plans_wrapper = "";
            if (!empty($user_id) && $user_id != 0) {
                $arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));
                $planIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
                $futurePlanIDs = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                $futurePlanIDs = !empty($futurePlanIDs) ? $futurePlanIDs : array();
                if( !empty( $futurePlanIDs ) ){
                    foreach( $futurePlanIDs as $fPlanKey => $fPlanId ){
                        $fPlanData = $this->arm_get_post_from_plan_id( $fPlanId );

                        if( !empty( $fPlanData[0]['arm_subscription_plan_id'] ) && !empty( $fPlanData[0]['arm_subscription_plan_post_id'] ) ){
                            $planIDs[$fPlanData[0]['arm_subscription_plan_id']] = $fPlanData[0]['arm_subscription_plan_post_id'];
                        }
                    }
                }

                $arm_paid_post_plans_wrapper = '';
                if (!empty($planIDs) || !empty($futurePlanIDs)) {
                
                $arm_paid_post_plans_wrapper .= '<div class="arm_add_member_plans_div arm_paid_post_plans_wrapper" data-user_id="'.$user_id.'">';

                $arm_paid_post_plans_wrapper.= '<table class="arm_user_plan_table">';
                $arm_paid_post_plans_wrapper.= '<tr class="odd">';
                $arm_paid_post_plans_wrapper.= '<th class="arm_user_plan_text_th arm_user_plan_no">'. __('No', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_user_plan_text_th arm_user_plan_name">'. __('Post Name', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_user_plan_text_th arm_user_plan_type">'. __('Post Type', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_user_plan_text_th arm_user_plan_start">'. __('Starts On', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_user_plan_text_th arm_user_plan_end">'. __('Expires On', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_user_plan_text_th arm_user_plan_cycle_date">'. __('Cycle Date', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_user_plan_text_th arm_user_plan_action">'. __('Action', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '</tr>';

                            $offset = (!empty($current_page) && $current_page > 1) ? (($current_page - 1) * $per_page) : 0;
                            $membership_count = count($planIDs);
                            $planIDs_slice = array_slice($planIDs, $offset, $per_page);
                            
                            $date_format = $arm_global_settings->arm_get_wp_date_format();
                            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                            
                            $count_plans = 0;
                            if( $current_page > 1 ){
                                $count_plans = $count_plans + $per_page;
                            }
                            if (!empty($planIDs)) {
                                foreach ($planIDs as $pID => $arm_paid_post_id) {
                                    $uniq_delete_no = uniqid();
                                    if (!empty($pID) && in_array($arm_paid_post_id, $planIDs_slice)) {
                                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $pID, true);
                                        $planData = !empty($planData) ? $planData : array();

                                        if (!empty($planData) && !empty($planData['arm_current_plan_detail']['arm_subscription_plan_post_id'])) {
                                            $planDetail = $planData['arm_current_plan_detail'];
                                            if (!empty($planDetail)) {
                                                $planObj = new ARM_Plan(0);
                                                $planObj->init((object) $planDetail);
                                            } else {
                                                $planObj = new ARM_Plan($pID);
                                            }

                                            $no = $count_plans;
                                            $planName = $planObj->name;
                                            $grace_message = '';
                                            
                                            $starts_date = !empty($planData['arm_start_plan']) ? $planData['arm_start_plan'] : '';
                                            $started_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : '';

                                            $starts_on = !empty($starts_date) ? date_i18n($date_format, $starts_date) : '-';

                                            if($started_date != '' && $started_date <= $starts_date) {
                                                $starts_on = date_i18n($date_format, $started_date);
                                            }

                                            $expires_on = !empty($planData['arm_expire_plan']) ? '<span id="arm_user_expiry_date_' . $pID . '" style="display: inline;"> ' . date_i18n($date_format, $planData['arm_expire_plan']) . ' <img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Change Expiry Date', 'ARMember') . '" data-plan_id="' . $pID . '" class="arm_edit_user_expiry_date"></span><span id="arm_user_expiry_date_box_' . $pID . '" style="display: none; position: relative; width: 155px;"><input type="text" value="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '"  data-date_format="'.$arm_common_date_format.'" name="arm_subscription_expiry_date_' . $pID . '" class="arm_member_form_input arm_user_plan_expiry_date_picker" style="width: 120px; min-width: 120px;"/><img src="' . MEMBERSHIP_IMAGES_URL . '/cancel_date_icon.png" width="11" height="11" title="' . __('Cancel', 'ARMember') . '" data-plan_id="' . $pID . '" data-plan-expire-date="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '" class="arm_cancel_edit_user_expiry_date"></span>' : __('Never Expires', 'ARMember');
                                            $renewal_on = !empty($planData['arm_next_due_payment']) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';
                                            $trial_starts = !empty($planData['arm_trial_start']) ? $planData['arm_trial_start'] : '';
                                            $trial_ends = !empty($planData['arm_trial_end']) ? $planData['arm_trial_end'] : '';
                                            $arm_payment_mode = ( $planData['arm_payment_mode'] == 'auto_debit_subscription') ? '<br/>(' . __('Auto Debit','ARMember') . ')' : '';
                                            $arm_payment_cycle = !empty($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';

                                            if ($planObj->is_recurring()) {
                                                $recurring_plan_options = $planObj->prepare_recurring_data($arm_payment_cycle);
                                                $recurring_time = $recurring_plan_options['rec_time'];
                                                $completed = $planData['arm_completed_recurring'];
                                                if ($recurring_time == 'infinite' || empty($planData['arm_expire_plan'])) {
                                                    $remaining_occurence = __('Never Expires', 'ARMember');
                                                } else {
                                                    $remaining_occurence = $recurring_time - $completed;
                                                }

                                                if (!empty($planData['arm_expire_plan'])) {
                                                    if ($remaining_occurence == 0) {
                                                        $renewal_on = __('No cycles due', 'ARMember');
                                                    } else {
                                                        $renewal_on .= "<br/>( " . $remaining_occurence . __(' cycles due', 'ARMember') . " )";
                                                    }
                                                }

                                                $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                                                $arm_grace_period_end = $planData['arm_grace_period_end'];

                                                if ($arm_is_user_in_grace == "1" || $arm_is_user_in_grace == 1) {
                                                    $arm_grace_period_end = date_i18n($date_format, $arm_grace_period_end);
                                                    $grace_message .= "<br/>( " . __('grace period expires on', 'ARMember') ." ". $arm_grace_period_end . " )";
                                                }
                                            }

                                            $arm_plan_is_suspended = '';

                                            if (!empty($suspended_plan_ids)) {
                                                if (in_array($pID, $suspended_plan_ids)) {
                                                    $arm_plan_is_suspended = '<div class="arm_user_plan_status_div" style="position: relative;"><span class="armhelptip tipso_style" id="arm_user_suspend_plan_' . $pID . '" style="color: red; cursor:pointer;" onclick="arm_show_failed_payment_history(' . $user_id . ',' . $pID . ',\'' . $planName . '\',\'' . $planData['arm_start_plan'] . '\')" title="' . __('Click here to Show failed payment history', 'ARMember') . '">(' . __('Suspended', 'ARMember') . ')</span><img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Activate Post', 'ARMember') . '" data-plan_id="' . $pID . '" onclick="showConfirmBoxCallback(\'change_user_plan_' . $pID . '\');" class="arm_change_user_plan_img_' . $pID . '">
                
                                                    <div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_change_user_plan_' . $pID . '" style="top:25px; right: -20px; ">
                                                            <div class="arm_confirm_box_body">
                                                                <div class="arm_confirm_box_arrow" style="float: right"></div>
                                                                <div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">' .
                                                            __('Are you sure you want to active this paid post?', 'ARMember') . '
                                                                </div>
                                                                <div class="arm_confirm_box_btn_container">
                                                                    <button type="button" class="arm_confirm_box_btn armemailaddbtn" id="arm_change_user_plan_status" style="margin-right: 5px;" data-index="' . $pID . '" >' . __('Ok', 'ARMember') . '</button>
                                                                    <button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">' . __('Cancel', 'ARMember') . '</button>
                                                                </div>
                                                            </div>
                                                        </div>

                                            </div>';
                                                }
                                            }

                                            $trial_active = '';
                                            if (!empty($trial_starts)) {
                                                if ($planData['arm_is_trial_plan'] == 1 || $planData['arm_is_trial_plan'] == '1') {
                                                    if ($trial_starts < $planData['arm_start_plan']) {
                                                        $trial_active = "<div class='arm_user_plan_status_div'><span class='arm_current_membership_trial_active'>(" . __('trial active', 'ARMember') . ")</span></div>";
                                                    }
                                                }
                                            }
                                            

                                            
                                                
                                            
                                            $count_plans_is_odd_even = ($count_plans % 2 == 0) ? 'even' : 'odd';
                                            $count_plans_new = $count_plans + 1;    
                                            $arm_paid_post_plans_wrapper.= '<tr class="arm_user_plan_table_tr '.$count_plans_is_odd_even.'" id="arm_user_plan_div_'.$uniq_delete_no.'">';
                                            $arm_paid_post_plans_wrapper.= '<td>'.$count_plans_new.'</td>';
                                            $arm_paid_post_plans_wrapper.= '<td>'.$planName . $arm_plan_is_suspended.'</td>';
                                            $arm_paid_post_plans_wrapper.= '<td>'.$planObj->new_user_plan_text(false, $arm_payment_cycle).'</td>';
                                            $arm_paid_post_plans_wrapper.= '<td>'.$starts_on . $trial_active.'</td>';
                                            $arm_paid_post_plans_wrapper.= '<td>'.$expires_on.'</td>';
                                            $arm_paid_post_plans_wrapper.= '<td>'.$renewal_on . $grace_message . $arm_payment_mode.'</td>';

                                            $arm_paid_post_plans_wrapper.= '<td>';

                                                    
                                            if ($planObj->is_recurring() && $planData['arm_payment_mode'] == 'manual_subscription' && !in_array($pID, $futurePlanIDs)) {

                                                $recurringData = $planObj->prepare_recurring_data($arm_payment_cycle);

                                                $total_recurrence = $recurringData['rec_time'];
                                                $completed_rec = $planData['arm_completed_recurring'];
                                                
                                                $arm_paid_post_plans_wrapper.= '<div style="position: relative; float: left;">';
                                                   
                                                    if (!in_array($pID, $suspended_plan_ids) && $total_recurrence != $completed_rec) {
                                                        
                                                        $arm_paid_post_plans_wrapper.= '<a href="javascript:void(0)" id="arm_extend_cycle_days" class="arm_user_extend_renewal_date_action_btn" onclick="showConfirmBoxCallback(\'extend_renewal_date_'.$pID.'\');">'.__('Extend Days', 'ARMember').'</a>';

                                                        $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_extend_renewal_date" id="arm_confirm_box_extend_renewal_date_'.$pID.'">';
                                                        $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box_body">';
                                                        $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box_arrow"></div>';
                                                        $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">';
                                                        $arm_paid_post_plans_wrapper.= '<span style="font-size: 15px; margin-bottom: 5px;">'.__('Select how many days you want to extend in current cycle?', 'ARMember').'</span><div style="margin-top: 10px;">';
                                                        $arm_paid_post_plans_wrapper.= '<input type="hidden" id="arm_user_grace_plus_'.$pID.'" name="arm_user_grace_plus_'.$pID.'" value="0" class="arm_user_grace_plus"/>';
                                                        $arm_paid_post_plans_wrapper.= '<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
                                                                            <dt style="min-width:45px; width:45px; text-align: center;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                            <dd>';
                                                        $arm_paid_post_plans_wrapper.= '<ul data-id="arm_user_grace_plus_'.$pID.'">';
                                                                                    
                                                                                    for ($i = 0; $i <= 30; $i++) {
                                                                                        
                                                                                        $arm_paid_post_plans_wrapper.= '<li data-label='.$i.' data-value='.$i.'>'.$i.'</li>';
                                                                                        
                                                                                    }
                                                                                    
                                                        $arm_paid_post_plans_wrapper.= '</ul>';
                                                        $arm_paid_post_plans_wrapper.= '</dd>';
                                                        $arm_paid_post_plans_wrapper.= '</dl>'.__('Days', 'ARMember').'</div>';
                                                        $arm_paid_post_plans_wrapper.= '</div>';
                                                        $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box_btn_container">';
                                                        $arm_paid_post_plans_wrapper.= '<button type="button" class="arm_confirm_box_btn armemailaddbtn" style="margin-right: 5px;" onclick="hideConfirmBoxCallback();">'.__('Ok', 'ARMember').'</button>';
                                                        $arm_paid_post_plans_wrapper.= '<button type="button" class="arm_confirm_box_btn armcancel arm_user_extend_renewal_date_cancel_btn" onclick="hideUserExtendRenewalDateBoxCallback('.$pID.');">'.__('Cancel', 'ARMember').'</button>';
                                                        
                                                        $arm_paid_post_plans_wrapper.= '</div>';
                                                            
                                                        $arm_paid_post_plans_wrapper.= '</div>';
                                                        $arm_paid_post_plans_wrapper.= '</div>';
                                                       
                                                    }
                                                    
                                                    
                                                    if ($total_recurrence != $completed_rec) {
                                                         
                                                        $arm_paid_post_plans_wrapper .= '<a href="javascript:void(0)" class="arm_user_renew_next_cycle_action_btn" id="arm_skip_next_cycle" onclick="showConfirmBoxCallback(\'renew_next_cycle_'.$pID.'\');">'.__('Renew Cycle', 'ARMember').'</a>';
                                                        $arm_paid_post_plans_wrapper .=  '<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_renew_next_cycle" id="arm_confirm_box_renew_next_cycle_'.$pID.'" style="width: 280px; top:25px; right:45px;">';
                                                        $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_body">';
                                                        $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_arrow" style="float: right"></div>';
                                                        $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">';
                                                        $arm_paid_post_plans_wrapper .= '<input type="hidden" id="arm_skip_next_renewal_'.$pID.'" name="arm_skip_next_renewal_'.$pID.'" value="0" class="arm_skip_next_renewal"/>'.__('Are you sure you want to renew next cycle?', 'ARMember').'</div>'; 
                                                        $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_btn_container">';
                                                        $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armemailaddbtn" style="margin-right: 5px;" onclick="RenewNextCycleOkCallback('.$pID.')">'.__('Ok', 'ARMember').'</button>';
                                                        $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armcancel arm_user_renew_next_cycle_cancel_btn" onclick="hideUserRenewNextCycleBoxCallback('.$pID.');">'.__('Cancel', 'ARMember').'</button>';
                                                        $arm_paid_post_plans_wrapper .= '</div>';
                                                        $arm_paid_post_plans_wrapper .= '</div>';
                                                        $arm_paid_post_plans_wrapper .= '</div>';
                                                    }
                                                }
                                                else if(isset($planData['arm_current_plan_detail']['arm_subscription_plan_type']) && $planData['arm_current_plan_detail']['arm_subscription_plan_type']=='paid_finite')
                                                {
                                                      
                                                    $arm_paid_post_plans_wrapper .= '<div style="position: relative; float: left;">';
                                                    $arm_paid_post_plans_wrapper .= '<a href="javascript:void(0)" class="arm_user_renew_next_cycle_action_btn" id="arm_skip_next_cycle" onclick="showConfirmBoxCallback(\'renew_next_cycle_'.$pID.'\');">'.__('Renew', 'ARMember').'</a>';
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_renew_next_cycle" id="arm_confirm_box_renew_next_cycle_'.$pID.'" style="width: 280px; top:25px; right:45px; ">';
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_body">';
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_arrow" style="float: right"></div>';
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">';
                                                    $arm_paid_post_plans_wrapper .= '<input type="hidden" id="arm_skip_next_renewal_'.$pID.'" name="arm_skip_next_renewal_'.$pID.'" value="0" class="arm_skip_next_renewal"/>'.__('Are you sure you want to renew plan?', 'ARMember').'</div>';
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_btn_container">';
                                                    $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armemailaddbtn" style="margin-right: 5px;" onclick="RenewNextCycleOkCallback('.$pID.')" >'.__('Ok', 'ARMember').'</button>';
                                                    $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armcancel arm_user_renew_next_cycle_cancel_btn" onclick="hideUserRenewNextCycleBoxCallback('.$pID.');">'.__('Cancel', 'ARMember').'</button>';
                                                    $arm_paid_post_plans_wrapper .= '</div>';
                                                    $arm_paid_post_plans_wrapper .= '</div>';
                                                    $arm_paid_post_plans_wrapper .= '</div>';
                                                       
                                                }

                                                if (in_array($pID, $suspended_plan_ids)) {
                                                    
                                                    $arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_user_suspended_plan[]" value="'.$pID.'" id="arm_user_suspended_plan_'.$pID.'"/>';
                                                    
                                                }
                                                    
                                                $arm_paid_post_plans_wrapper .= '<div style="position: relative; float: left;">';
                                                
                                                $arm_paid_post_plans_wrapper .= '<a class="arm_remove_user_plan_div armhelptip tipso_style" href="javascript:void(0)" title="'.__('Remove Post', 'ARMember').'" onclick="showConfirmBoxCallback(\'delete_user_plan_'.$pID.'\');"></a>';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_delete_user_plan_'.$pID.'" style="top:25px; right: -20px; ">';
                                                    
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_body">';
                                                
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_arrow" style="float: right"></div>';

                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">'.__('Are you sure you want to remove this post?', 'ARMember').'</div>'; 
                                                
                                                
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_btn_container">';
                                                $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armemailaddbtn arm_remove_user_plan_div_box" style="margin-right: 5px;" data-index='.$uniq_delete_no.' >'.__('Ok', 'ARMember').'</button>';

                                                $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">'.__('Cancel', 'ARMember').'</button>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div></div>';
                                                
                                                $arm_paid_post_plans_wrapper .= '</td>';


                                            
                                            $arm_paid_post_plans_wrapper .= '</tr>';

                                                $count_plans++;
                                        } else {
                                            if (!empty($pID)) {
                                                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $pID, true);
                                                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                                $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                                if (!empty($planData)) {
                                                    $planDetail = $planData['arm_current_plan_detail'];
                                                    if (!empty($planDetail)) {
                                                        $planObj = new ARM_Plan(0);
                                                        $planObj->init((object) $planDetail);
                                                    } else {
                                                        $planObj = new ARM_Plan($pID);
                                                    }
                                                }

                                                $no = $count_plans;
                                                $planName = $planObj->name;
                                                $grace_message = '';
                                                $starts_date = !empty($planData['arm_start_plan']) ? $planData['arm_start_plan'] : '';
                                                $started_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : '';

                                                $starts_on = !empty($starts_date) ? date_i18n($date_format, $starts_date) : '-';

                                                if($started_date != '' && $started_date <= $starts_date) {
                                                    $starts_on = date_i18n($date_format, $started_date);
                                                }
                                                $expires_on = !empty($planData['arm_expire_plan']) ? '<span id="arm_user_expiry_date_' . $pID . '" style="display: inline;">' . date_i18n($date_format, $planData['arm_expire_plan']) . ' <img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Change Expiry Date', 'ARMember') . '" data-plan_id="' . $pID . '" class="arm_edit_user_expiry_date"></span><span id="arm_user_expiry_date_box_' . $pID . '" style="display: none; position: relative; width: 155px;"><input type="text" value="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '" data-date_format="'.$arm_common_date_format.'"  name="arm_subscription_expiry_date_' . $pID . '" class="arm_member_form_input arm_user_plan_expiry_date_picker" style="width: 120px; min-width: 120px;"/><img src="' . MEMBERSHIP_IMAGES_URL . '/cancel_date_icon.png" width="11" height="11" title="' . __('Cancel', 'ARMember') . '" data-plan_id="' . $pID . '" data-plan-expire-date="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '" class="arm_cancel_edit_user_expiry_date"></span>' : __('Never Expires', 'ARMember');
                                                $renewal_on = !empty($planData['arm_next_due_payment']) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';
                                                $trial_starts = !empty($planData['arm_trial_start']) ? $planData['arm_trial_start'] : '';
                                                $trial_ends = !empty($planData['arm_trial_end']) ? $planData['arm_trial_end'] : '';
                                                $arm_payment_mode = ( $planData['arm_payment_mode'] == 'auto_debit_subscription') ? '<br/>(' . __('Auto Debit','ARMember') . ')' : '';
                                                $arm_payment_cycle = !empty($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';
                                                if ($planObj->is_recurring()) {
                                                    $recurring_plan_options = $planObj->prepare_recurring_data($arm_payment_cycle);
                                                    $recurring_time = $recurring_plan_options['rec_time'];
                                                    $completed = $planData['arm_completed_recurring'];
                                                    if ($recurring_time == 'infinite' || empty($planData['arm_expire_plan'])) {
                                                        $remaining_occurence = __('Never Expires', 'ARMember');
                                                    } else {
                                                        $remaining_occurence = $recurring_time - $completed;
                                                    }

                                                    if (!empty($planData['arm_expire_plan'])) {
                                                        if ($remaining_occurence == 0) {
                                                            $renewal_on = __('No cycles due', 'ARMember');
                                                        } else {
                                                            $renewal_on .= "<br/>( " . $remaining_occurence . __(' cycles due', 'ARMember') . " )";
                                                        }
                                                    }
                                                    $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                                                    $arm_grace_period_end = $planData['arm_grace_period_end'];

                                                    if ($arm_is_user_in_grace == "1") {
                                                        $arm_grace_period_end = date_i18n($date_format, $arm_grace_period_end);
                                                        $grace_message .= "<br/>( " . __('grace period expires on', 'ARMember') ." ". $arm_grace_period_end . " )";
                                                    }
                                                }

                                                $arm_plan_is_suspended = '';

                                                $trial_active = '';
                                                $plans_is_odd_even =($count_plans % 2 == 0) ? 'even' : 'odd';

                                                $arm_paid_post_plans_wrapper .= '<tr class="arm_user_plan_table_tr '.$plans_is_odd_even.'" id="arm_user_future_plan_div_'.$count_plans.'">';
                                                $count_plans_no = $no + 1;
                                                $arm_paid_post_plans_wrapper .= '<td>'.$count_plans_no.'</td>';

                                                $arm_paid_post_plans_wrapper .= '<td>'.$planName . $arm_plan_is_suspended.'</td>';
                                                $arm_paid_post_plans_wrapper .= '<td>'.$planObj->new_user_plan_text(false, $arm_payment_cycle).'</td>';
                                                $arm_paid_post_plans_wrapper .= '<td>'.$starts_on . $trial_active.'</td>';
                                                $arm_paid_post_plans_wrapper .= '<td>'.$expires_on.'</td>';
                                                $arm_paid_post_plans_wrapper .= '<td>'.$renewal_on . $grace_message . $arm_payment_mode.'</td>';

                                                $arm_paid_post_plans_wrapper .= '<td>';
                                                
                                                
                                                $arm_paid_post_plans_wrapper .= '<div style="position: relative; float: left;">';
                                                $arm_paid_post_plans_wrapper .= '<a class="arm_remove_user_plan_div armhelptip tipso_style" href="javascript:void(0)" title="'.__('Remove Post', 'ARMember').'" onclick="showConfirmBoxCallback(\'delete_user_plan_'.$pID.'\');"></a>';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_delete_user_plan_'.$pID.'" style="top:25px; right: -20px; ">';

                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_body">';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_arrow" style="float: right"></div>';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">'.__('Are you sure you want to remove this post?', 'ARMember').'</div>';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_btn_container">';
                                                $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armemailaddbtn" id="arm_remove_paid_post_user_future_plan_div_'.$pID.'" style="margin-right: 5px;" data-index='.$count_plans.'>'.__('Ok', 'ARMember').'</button>';
                                                $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">'.__('Cancel', 'ARMember').'</button>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                
                                                $arm_paid_post_plans_wrapper .=  '</td>';
                                                $arm_paid_post_plans_wrapper .= '</tr>';

                                                $count_plans++;
                                            }
                                        }
                                    }

                                    if( in_array( $pID, $futurePlanIDs ) ){
                                        $arm_paid_post_plans_wrapper .= '<input name="arm_user_future_plan[]" value='.$pID.' type="hidden" id="arm_user_paid_post_future_plan_'.$uniq_delete_no.'">';
                                    } else {
                                        //$arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_user_plan[]" value="'.$pID.'"/>';
                                        $arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_user_plan2[]" id="arm_user_paid_post_div_'.$uniq_delete_no.'" value="'.$pID.'"/>';
                                        
                                        $arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_subscription_start_date2[]" id="arm_user_paid_post_date_div_'.$uniq_delete_no.'" value='.date('m/d/Y', (int)$planData['arm_start_plan']).' />';
                                    }
                                    //$arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_subscription_start_date[]" value='.date('m/d/Y', $planData['arm_start_plan']).' />';
                                }
                            }

                                 
                    $arm_paid_post_plans_wrapper .= '</table>';
                    

                    if(!empty($planIDs) && $membership_count>5){
                        $member_paid_post_plans_pagging = $arm_global_settings->arm_get_paging_links($current_page, $membership_count, $per_page);
                        $arm_paid_post_plans_wrapper .= '<div class="arm_member_paid_post_plans_pagination_block">';
                        $arm_paid_post_plans_wrapper .= '<div class="arm_member_paid_post_plans_paging_container">'.$member_paid_post_plans_pagging.'</div>';
                        $arm_paid_post_plans_wrapper .= '</div>';
                    }
                     
                $arm_paid_post_plans_wrapper .= '</div>';
                }
            }
            return  $arm_paid_post_plans_wrapper;
        }
	
        function arm_paid_post_plan_paging_action() {
            global $wp, $wpdb, $ARMember, $arm_global_settings,$arm_subscription_plans;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_paid_post_plan_paging_action') {
                
                $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
                $current_page = isset($_POST['page']) ? $_POST['page'] : 1;
                $per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 5;
                
                echo $this->arm_get_paid_post_plans_paging($user_id, $current_page, $per_page);
            }
            exit;
        }

        function arm_paid_post_content_check_restriction($content)
        {
            // Check if we're inside the main loop in a single Post.
            if (!current_user_can('administrator') && is_singular() && in_the_loop() && is_main_query() ) 
            {
                $current_post_id = get_the_ID();
                if(!empty($current_post_id))
                {
                    $arm_is_paid_post = get_post_meta($current_post_id, 'arm_is_paid_post', true);
                    if(!empty($arm_is_paid_post))
                    {
                        $plan_id = $this->arm_get_plan_from_post_id( $current_post_id );
                        if( !empty( $plan_id ) )
                        {
                            $hasaccess = false;
                            $isLoggedIn = is_user_logged_in();
                            if($isLoggedIn)
                            {
                                $current_user_id = get_current_user_id();
                                $arm_user_plan = get_user_meta($current_user_id, 'arm_user_plan_ids', true);
                                $arm_user_plan = !empty($arm_user_plan) ? $arm_user_plan : array();
                                if(!empty($arm_user_plan)){
                                    $suspended_plan_ids = get_user_meta($current_user_id, 'arm_user_suspended_plan_ids', true);
                                    if( ! empty($suspended_plan_ids)) {
                                        foreach ($suspended_plan_ids as $suspended_plan_id) {
                                            if(in_array($suspended_plan_id, $arm_user_plan)) {
                                                unset($arm_user_plan[array_search($suspended_plan_id, $arm_user_plan)]);
                                            }
                                        }
                                    }

                                    if(in_array($plan_id, $arm_user_plan))
                                    {
                                        $hasaccess = true;
                                    }
                                }
                            }

                            if($hasaccess==false)
                            {

                                $arm_enable_paid_post_alternate_content = get_post_meta($current_post_id, 'arm_enable_paid_post_alternate_content', true);
                                if(!empty($arm_enable_paid_post_alternate_content))
                                {
                                    $arm_paid_post_alternative_content = get_post_meta($current_post_id, 'arm_paid_post_alternative_content', true);
                                    return $arm_paid_post_alternative_content;
                                }
                                else {
                                    global $arm_global_settings;
                                    $arm_global_settings_general_settings = !empty($arm_global_settings->global_settings['arm_pay_per_post_default_content']) ? stripslashes($arm_global_settings->global_settings['arm_pay_per_post_default_content']) : __('Content is Restricted. Buy this post to get access to full content.', 'ARMember');

                                    return $arm_global_settings_general_settings;
                                    
                                }
                            }

                            
                        }
                        
                    }
                }
                
            }
 
            return $content;
        }
	
	function arm_paid_post_plan_modal_paging_action()
        {
            global $wp, $wpdb, $ARMember, $arm_global_settings,$arm_subscription_plans;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_paid_post_plan_modal_paging_action') {
                
                $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
                $current_page = isset($_POST['page']) ? $_POST['page'] : 1;
                $per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 5;
                
                echo $this->arm_get_paid_post_modal_plans($user_id, $current_page, $per_page);
            }
            exit;   
        }


        function arm_get_paid_post_modal_plans($user_id = 0, $current_page = 1, $per_page = 5)
        {
            global $arm_global_settings,$arm_subscription_plans,$is_multiple_membership_feature, $ARMember, $arm_capabilities_global;

            $arm_paid_post_plans_wrapper = "";
            if (!empty($user_id) && $user_id != 0) {
                $arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));

                $planIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
                $planIDs = !empty($planIDs) ? $planIDs : array();

                /*$postIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
                $postIDs = !empty($postIDs) ? $postIDs : array();*/

                $futurePlanIDs = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                $futurePlanIDs = !empty($futurePlanIDs) ? $futurePlanIDs : array();

                if( !empty( $futurePlanIDs ) ){
                    foreach( $futurePlanIDs as $fPlanKey => $fPlanId ){
                        $fPlanData = $this->arm_get_post_from_plan_id( $fPlanId );

                        if( !empty( $fPlanData[0]['arm_subscription_plan_id'] ) && !empty($fPlanData[0]['arm_subscription_plan_post_id']) ){
                            $planIDs[$fPlanData[0]['arm_subscription_plan_id']] = $fPlanData[0]['arm_subscription_plan_post_id'];
                        }
                    }
                }

                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1');
                $date_format = $arm_global_settings->arm_get_wp_date_format();

                $user_name = '';
                $arm_user_info = get_userdata($user_id);
                $user_name = $arm_user_info->user_login;
                $u_roles = $arm_user_info->roles;
                
                

                $all_subscription_plans = $arm_subscription_plans->arm_get_paid_post_data();

                /*foreach($planIDs as $plan_key => $plan_value)
                {
                    if(!array_key_exists($plan_value, $postIDs))
                    {
                        unset($plan_key);
                    }
                }*/

                /*$all_plan_ids = array();
                if (!empty($all_subscription_plans)) {
                    foreach ($all_subscription_plans as $p) {
                        $all_plan_ids[] = $p['arm_subscription_plan_id'];
                    }
                }*/



                $plansLists = '<li data-label="' . __('Select Post', 'ARMember') . '" data-value="">' . __('Select Post', 'ARMember') . '</li>';
                if (!empty($all_subscription_plans)) {
                    foreach ($all_subscription_plans as $p) {
                        if($p['arm_subscription_plan_post_id'] != 0 && (!in_array($p['arm_subscription_plan_id'], $planIDs)))
                        {
                            $p_id = $p['arm_subscription_plan_id'];
                            $plansLists .= '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . $p_id . '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                        }
                    }
                }


                $arm_paid_post_plans_wrapper .= '<div class="arm_add_new_item_box arm_add_new_plan"><a id="arm_add_plan_to_user" class="greensavebtn arm_save_btn" href="javascript:void(0)" ><img align="absmiddle" src="' . MEMBERSHIP_IMAGES_URL . '/add_new_icon.png"><span> ' . __('Add Post', 'ARMember') . '</span></a></div>';

                $arm_paid_post_plans_wrapper .= '<div class="popup_content_text arm_add_plan" style="text-align:center; display:none;">';
                $arm_paid_post_plans_wrapper .= '<div class="arm_edit_plan_wrapper" style="position: relative; margin-top: 10px;">';
                $arm_paid_post_plans_wrapper .= '<span class="arm_edit_plan_lbl">' . __('Select Post', 'ARMember') . '*</span> ';
                $arm_paid_post_plans_wrapper .= '<div class="arm_edit_field">';
                
                    $arm_paid_post_plans_wrapper .= '<input type="hidden" class="arm_user_plan_change_input arm_user_plan_change_input_get_cycle" name="arm_user_plan[]" id="arm_user_plan" value="" data-manage-plan-grid="1"/>';
                
                $arm_paid_post_plans_wrapper .= '<dl class="arm_selectbox column_level_dd arm_member_form_dropdown" style="float: left;">';
                $arm_paid_post_plans_wrapper .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                $arm_paid_post_plans_wrapper .= '<dd><ul data-id="arm_user_plan">' . $plansLists . '</ul></dd>';
                $arm_paid_post_plans_wrapper .= '</dl>';
                $arm_paid_post_plans_wrapper .= '<br/><span class="arm_error_select_plan error arm_invalid" style="display:none; text-align:left;">' . __('Please select Post.', 'ARMember') . '</span>';
                $arm_paid_post_plans_wrapper .= '</div>';
                $arm_paid_post_plans_wrapper .= '</div>';

                $arm_paid_post_plans_wrapper .= '<div class="arm_selected_plan_cycle" style="position: relative; margin-top: 3.8rem;">';
                $arm_paid_post_plans_wrapper .= '</div>';

                $arm_paid_post_plans_wrapper .= '<div  style="position: relative; margin-top: 10px;">';
                $arm_paid_post_plans_wrapper .= '<span class="arm_edit_plan_lbl">' . __('Post Start Date', 'ARMember') . '</span>';
                $arm_paid_post_plans_wrapper .= '<div class="arm_edit_field" style="position: relative;">';
                
                $arm_paid_post_plans_wrapper .= '<input type="text" value="' . date($arm_common_date_format, strtotime(date('Y-m-d'))) . '"  data-date_format="'.$arm_common_date_format.'" name="arm_subscription_start_date[]" class="arm_datepicker arm_member_form_input arm_user_add_plan_date_picker"  style="width: 500px; min-width: 500px;"/>';
                
                $arm_paid_post_plans_wrapper .= '</div>';
                $arm_paid_post_plans_wrapper .= '</div>';

                $arm_paid_post_plans_wrapper .= '<div  style="position: relative; margin-top: 10px;">';
                $arm_paid_post_plans_wrapper .= '<span class="arm_edit_plan_lbl">&nbsp;</span>';
                $arm_paid_post_plans_wrapper .= '<div class="arm_edit_field">';
                $arm_paid_post_plans_wrapper .= '<button class="arm_member_add_paid_plan_save_btn arm_save_btn">' . __('Save', 'ARMember') . '</button>';

                
                $arm_paid_post_plans_wrapper .= '<button class="arm_add_plan_cancel_btn arm_cancel_btn" type="button">' . __('Close', 'ARMember') . '</button>';


                $arm_paid_post_plans_wrapper .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" class="arm_loader_img_user_add_plan" style="position:relative;top:8px;display:none;" width="24" height="24" />';
                $arm_paid_post_plans_wrapper .= '</div>';
                $arm_paid_post_plans_wrapper .= '</div>';

                $arm_paid_post_plans_wrapper .= '</div>';

                $user_plans = $planIDs;
                //$user_plans = $postIDs;

                if (!empty($u_roles)) {
                    foreach ($u_roles as $ur) {
                        $arm_paid_post_plans_wrapper .= '<input type="hidden" name="roles[]" value="' . $ur . '"/>';
                    }
                }
                
                $arm_paid_post_plans_wrapper .= '<div class="arm_loading_grid arm_plan_loading_grid" style="display: none;"><img src="' . MEMBERSHIP_IMAGES_URL . '/loader.gif" alt="Loading.."></div>';

                $arm_paid_post_plans_wrapper .= '<div class="arm_paid_post_plans_wrapper" data-user_id="'.$user_id.'">';

                $arm_paid_post_plans_wrapper.= '<table class="arm_user_edit_plan_table" cellspacing="1" style="text-align: center; width:95%;     border-left: 1px solid #eaeaea; margin: 20px; border-right: 1px solid #eaeaea;">';
                $arm_paid_post_plans_wrapper.= '<tr class="arm_user_plan_row arm_user_plan_head odd">';
                //$arm_paid_post_plans_wrapper.= '<th class="arm_user_plan_text_th arm_user_plan_no">'. __('No', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_edit_plan_name">'. __('Post Name', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_edit_plan_type">'. __('Post Type', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_edit_plan_start">'. __('Starts On', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_edit_plan_expire">'. __('Expires On', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_edit_plan_cycle_date">'. __('Cycle Date', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '<th class="arm_edit_plan_action">'. __('Action', 'ARMember').'</th>';
                $arm_paid_post_plans_wrapper.= '</tr>';


                $offset = (!empty($current_page) && $current_page > 1) ? (($current_page - 1) * $per_page) : 0;
                $membership_count = count($planIDs);
                $planIDs_slice = array_slice($planIDs, $offset, $per_page);


                $date_format = $arm_global_settings->arm_get_wp_date_format();
                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                
                $count_plans = 0;
                if( $current_page > 1 ){
                    $count_plans = $count_plans + $per_page;
                }
                $arm_check_data = 1;
                
                $arm_member_view_paid_plan_detail_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';

                $arm_paid_post_supended_tooltip_class = $arm_paid_post_supended_tooltip_txt = "";
                $arm_paid_post_suspended_txt_func = "javascript:void(0)";
                if(empty($arm_member_view_paid_plan_detail_action))
                {
                    $arm_paid_post_supended_tooltip_class = "armhelptip tipso_style";
                    $arm_paid_post_supended_tooltip_txt = __('Click here to Show failed payment history', 'ARMember');
                    $arm_paid_post_suspended_txt_func = 'arm_show_failed_payment_history(' . $user_id . ',' . $pID . ',\'' . $planName . '\',\'' . $planData['arm_start_plan'] . '\')';
                }

                if (!empty($planIDs)) {
                    foreach ($planIDs as $pID => $arm_paid_post_id) {
                        if (!empty($pID) && in_array($arm_paid_post_id, $planIDs_slice)) {
                            $planData = get_user_meta($user_id, 'arm_user_plan_' . $pID, true);
                            $planData = !empty($planData) ? $planData : array();

                            $uniq_delete_no = uniqid();
                            if (!empty($planData) && !empty($planData['arm_current_plan_detail']['arm_subscription_plan_post_id'])) {
                                $planDetail = $planData['arm_current_plan_detail'];
                                if (!empty($planDetail)) {
                                    $planObj = new ARM_Plan(0);
                                    $planObj->init((object) $planDetail);
                                } else {
                                    $planObj = new ARM_Plan($pID);
                                }

                                $no = $count_plans;
                                $planName = $planObj->name;
                                $grace_message = '';
                                
                                $starts_date = !empty($planData['arm_start_plan']) ? $planData['arm_start_plan'] : '';
                                $started_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : '';

                                $starts_on = !empty($starts_date) ? date_i18n($date_format, $starts_date) : '-';

                                if($started_date != '' && $started_date <= $starts_date) {
                                    $starts_on = date_i18n($date_format, $started_date);
                                }

                                $expires_on = !empty($planData['arm_expire_plan']) ? '<span id="arm_user_expiry_date_' . $pID . '" style="display: inline;"> ' . date_i18n($date_format, $planData['arm_expire_plan']) . ' <img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Change Expiry Date', 'ARMember') . '" data-plan_id="' . $pID . '" class="arm_edit_user_expiry_date armhelptip tipso_style"></span><span id="arm_user_expiry_date_box_' . $pID . '" style="display: none; position: relative; width: 157px;"><input type="text" value="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '"  data-date_format="'.$arm_common_date_format.'" name="arm_subscription_expiry_date_' . $pID . '"  id="arm_subscription_expiry_date_'.$pID.'_'.$user_id.'" class="arm_member_form_input arm_user_plan_expiry_date_picker" style="width: 120px; min-width: 120px;"/><img src="' . MEMBERSHIP_IMAGES_URL . '/arm_save_icon.png" style="vertical-align: middle;display:none;" width="14" height="16" title="' . __('Save Expiry Date', 'ARMember') . '" class="arm_edit_plan_action_button arm_member_save_post armhelptip tipso_style" id="arm_member_save_post_'.$pID.'" data-plan_id="' . $pID . '" data-user_id="' . $user_id . '" /><img src="' . MEMBERSHIP_IMAGES_URL . '/cancel_date_icon.png" width="11" height="11" title="' . __('Cancel', 'ARMember') . '" data-plan_id="' . $pID . '" data-plan-expire-date="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '" class="arm_cancel_edit_user_expiry_date"></span>' : __('Never Expires', 'ARMember');
                                $renewal_on = !empty($planData['arm_next_due_payment']) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';
                                $trial_starts = !empty($planData['arm_trial_start']) ? $planData['arm_trial_start'] : '';
                                $trial_ends = !empty($planData['arm_trial_end']) ? $planData['arm_trial_end'] : '';
                                $arm_payment_mode = ( $planData['arm_payment_mode'] == 'auto_debit_subscription') ? '<br/>(' . __('Auto Debit','ARMember') . ')' : '';
                                $arm_payment_cycle = !empty($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';

                                if ($planObj->is_recurring()) {
                                    $recurring_plan_options = $planObj->prepare_recurring_data($arm_payment_cycle);
                                    $recurring_time = $recurring_plan_options['rec_time'];
                                    $completed = $planData['arm_completed_recurring'];
                                    if ($recurring_time == 'infinite' || empty($planData['arm_expire_plan'])) {
                                        $remaining_occurence = __('Never Expires', 'ARMember');
                                    } else {
                                        $remaining_occurence = $recurring_time - $completed;
                                    }

                                    if (!empty($planData['arm_expire_plan'])) {
                                        if ($remaining_occurence == 0) {
                                            $renewal_on = __('No cycles due', 'ARMember');
                                        } else {
                                            $renewal_on .= "<br/>( " . $remaining_occurence . __(' cycles due', 'ARMember') . " )";
                                        }
                                    }

                                    $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                                    $arm_grace_period_end = $planData['arm_grace_period_end'];

                                    if ($arm_is_user_in_grace == "1" || $arm_is_user_in_grace == 1) {
                                        $arm_grace_period_end = date_i18n($date_format, $arm_grace_period_end);
                                        $grace_message .= "<br/>( " . __('grace period expires on', 'ARMember') ." ". $arm_grace_period_end . " )";
                                    }
                                }

                                $arm_plan_is_suspended = '';

                                if (!empty($suspended_plan_ids)) {
                                    if (in_array($pID, $suspended_plan_ids)) {
                                        $arm_plan_is_suspended = '<div class="arm_user_plan_status_div" style="position: relative;"><span class="'.$arm_paid_post_supended_tooltip_class.'" id="arm_user_suspend_plan_' . $pID . '" style="color: red;" onclick="'. $arm_paid_post_suspended_txt_func.'" title="' . $arm_paid_post_supended_tooltip_txt . '">(' . __('Suspended', 'ARMember') . ')</span><img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Activate Post', 'ARMember') . '" data-plan_id="' . $pID . '" onclick="showConfirmBoxCallback(\'change_user_plan_' . $pID . '\');" class="arm_change_user_plan_img_' . $pID . '">
    
                                        <div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_change_user_plan_' . $pID . '" style="top:25px; right: -20px; ">
                                                <div class="arm_confirm_box_body">
                                                    <div class="arm_confirm_box_arrow" style="float: right"></div>
                                                    <div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">' .
                                                __('Are you sure you want to active this paid post?', 'ARMember') . '
                                                    </div>
                                                    <div class="arm_confirm_box_btn_container">
                                                        <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_post_status_change" style="margin-right: 5px;" data-index="' . $pID . '" data-item_id="'.$pID.'" >' . __('Ok', 'ARMember') . '</button>
                                                        <button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">' . __('Cancel', 'ARMember') . '</button>
                                                    </div>
                                                </div>
                                            </div>

                                </div>';
                                    }
                                }

                                $trial_active = '';
                                if (!empty($trial_starts)) {
                                    if ($planData['arm_is_trial_plan'] == 1 || $planData['arm_is_trial_plan'] == '1') {
                                        if ($trial_starts < $planData['arm_start_plan']) {
                                            $trial_active = "<div class='arm_user_plan_status_div'><span class='arm_current_membership_trial_active'>(" . __('trial active', 'ARMember') . ")</span></div>";
                                        }
                                    }
                                }
                                
                                $count_plans_is_odd_even = ($count_plans % 2 == 0) ? 'even' : 'odd';
                                $count_plans_new = $count_plans + 1;    
                                $arm_paid_post_plans_wrapper.= '<tr class="arm_user_plan_row '.$count_plans_is_odd_even.'" id="arm_user_plan_div_'.$uniq_delete_no.'">';
                                //$arm_paid_post_plans_wrapper.= '<td>'.$count_plans_new.'</td>';
                                $arm_paid_post_plans_wrapper.= '<td class="arm_edit_plan_name">'.$planName . $arm_plan_is_suspended.'</td>';
                                $arm_paid_post_plans_wrapper.= '<td class="arm_edit_plan_type">'.$planObj->new_user_plan_text(false, $arm_payment_cycle).'</td>';
                                $arm_paid_post_plans_wrapper.= '<td class="arm_edit_plan_start">'.$starts_on . $trial_active.'</td>';
                                $arm_paid_post_plans_wrapper.= '<td class="arm_edit_plan_expiry">'.$expires_on.'</td>';
                                $arm_paid_post_plans_wrapper.= '<td class="arm_edit_plan_cycle_date">'.$renewal_on . $grace_message . $arm_payment_mode.'</td>';

                                $arm_paid_post_plans_wrapper.= '<td>';

                                        
                                        if ($planObj->is_recurring() && $planData['arm_payment_mode'] == 'manual_subscription' && !in_array($pID, $futurePlanIDs)) {

                                            $recurringData = $planObj->prepare_recurring_data($arm_payment_cycle);

                                            $total_recurrence = $recurringData['rec_time'];
                                            $completed_rec = $planData['arm_completed_recurring'];
                                            
                                            $arm_paid_post_plans_wrapper.= '<div style="position: relative; float: left;">';
                                               
                                                if (!in_array($pID, $suspended_plan_ids) && $total_recurrence != $completed_rec) {
                                                    
                                                    //$arm_paid_post_plans_wrapper.= '<a href="javascript:void(0)" id="arm_extend_cycle_days" class="arm_user_extend_renewal_date_action_btn" onclick="showConfirmBoxCallback(\'extend_renewal_date_'.$pID.'\');">'.__('Extend Days', 'ARMember').'</a>';

                                                    $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_extend_renewal_date" id="arm_confirm_box_extend_renewal_date_'.$pID.'">';
                                                    $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box_body">';
                                                    $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box_arrow"></div>';
                                                    $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">';
                                                    $arm_paid_post_plans_wrapper.= '<span style="font-size: 15px; margin-bottom: 5px;">'.__('Select how many days you want to extend in current cycle?', 'ARMember').'</span><div style="margin-top: 10px;">';
                                                    $arm_paid_post_plans_wrapper.= '<input type="hidden" id="arm_user_grace_plus_'.$pID.'" name="arm_user_grace_plus_'.$pID.'" value="0" class="arm_user_grace_plus"/>';
                                                    $arm_paid_post_plans_wrapper.= '<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">
                                                                        <dt style="min-width:45px; width:45px; text-align: center;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                                                        <dd>';
                                                    $arm_paid_post_plans_wrapper.= '<ul data-id="arm_user_grace_plus_'.$pID.'">';
                                                                                
                                                                                for ($i = 0; $i <= 30; $i++) {
                                                                                    
                                                                                    $arm_paid_post_plans_wrapper.= '<li data-label='.$i.' data-value='.$i.'>'.$i.'</li>';
                                                                                    
                                                                                }
                                                                                
                                                    $arm_paid_post_plans_wrapper.= '</ul>';
                                                    $arm_paid_post_plans_wrapper.= '</dd>';
                                                    $arm_paid_post_plans_wrapper.= '</dl>'.__('Days', 'ARMember').'</div>';
                                                    $arm_paid_post_plans_wrapper.= '</div>';
                                                    $arm_paid_post_plans_wrapper.= '<div class="arm_confirm_box_btn_container">';
                                                    $arm_paid_post_plans_wrapper.= '<button type="button" class="arm_confirm_box_btn armemailaddbtn" style="margin-right: 5px;" onclick="hideConfirmBoxCallback();">'.__('Ok', 'ARMember').'</button>';
                                                    $arm_paid_post_plans_wrapper.= '<button type="button" class="arm_confirm_box_btn armcancel arm_user_extend_renewal_date_cancel_btn" onclick="hideUserExtendRenewalDateBoxCallback('.$pID.');">'.__('Cancel', 'ARMember').'</button>';
                                                    
                                                    $arm_paid_post_plans_wrapper.= '</div>';
                                                        
                                                    $arm_paid_post_plans_wrapper.= '</div>';
                                                    $arm_paid_post_plans_wrapper.= '</div>';
                                                   
                                                }
                                                
                                                
                                                if ($total_recurrence != $completed_rec) {
                                                     
                                                    //$arm_paid_post_plans_wrapper .= '<a href="javascript:void(0)" class="arm_user_renew_next_cycle_action_btn" id="arm_skip_next_cycle" onclick="showConfirmBoxCallback(\'renew_next_cycle_'.$pID.'\');">'.__('Renew Cycle', 'ARMember').'</a>';
                                                    $arm_paid_post_plans_wrapper .=  '<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_renew_next_cycle" id="arm_confirm_box_renew_next_cycle_'.$pID.'" style="width: 280px; top:25px; right:45px;">';
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_body">';
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_arrow" style="float: right"></div>';
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">';
                                                    $arm_paid_post_plans_wrapper .= '<input type="hidden" id="arm_skip_next_renewal_'.$pID.'" name="arm_skip_next_renewal_'.$pID.'" value="0" class="arm_skip_next_renewal"/>'.__('Are you sure you want to renew next cycle?', 'ARMember').'</div>'; 
                                                    $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_btn_container">';
                                                    $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armemailaddbtn" style="margin-right: 5px;" onclick="RenewNextCycleOkCallback('.$pID.')">'.__('Ok', 'ARMember').'</button>';
                                                    $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armcancel arm_user_renew_next_cycle_cancel_btn" onclick="hideUserRenewNextCycleBoxCallback('.$pID.');">'.__('Cancel', 'ARMember').'</button>';
                                                    $arm_paid_post_plans_wrapper .= '</div>';
                                                    $arm_paid_post_plans_wrapper .= '</div>';
                                                    $arm_paid_post_plans_wrapper .= '</div>';
                                                }
                                            }
                                            else if(isset($planData['arm_current_plan_detail']['arm_subscription_plan_type']) && $planData['arm_current_plan_detail']['arm_subscription_plan_type']=='paid_finite')
                                            {
                                                  
                                                $arm_paid_post_plans_wrapper .= '<div style="position: relative; float: left;">';
                                                //$arm_paid_post_plans_wrapper .= '<a href="javascript:void(0)" class="arm_user_renew_next_cycle_action_btn" id="arm_skip_next_cycle" onclick="showConfirmBoxCallback(\'renew_next_cycle_'.$pID.'\');">'.__('Renew', 'ARMember').'</a>';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_renew_next_cycle" id="arm_confirm_box_renew_next_cycle_'.$pID.'" style="width: 280px; top:25px; right:45px; ">';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_body">';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_arrow" style="float: right"></div>';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_text" style="text-align: center;padding-top: 15px;">';
                                                $arm_paid_post_plans_wrapper .= '<input type="hidden" id="arm_skip_next_renewal_'.$pID.'" name="arm_skip_next_renewal_'.$pID.'" value="0" class="arm_skip_next_renewal"/>'.__('Are you sure you want to renew plan?', 'ARMember').'</div>';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_btn_container">';
                                                $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armemailaddbtn" style="margin-right: 5px;" onclick="RenewNextCycleOkCallback('.$pID.')" >'.__('Ok', 'ARMember').'</button>';
                                                $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armcancel arm_user_renew_next_cycle_cancel_btn" onclick="hideUserRenewNextCycleBoxCallback('.$pID.');">'.__('Cancel', 'ARMember').'</button>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                   
                                            }

                                            if (in_array($pID, $suspended_plan_ids)) {
                                                
                                                $arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_user_suspended_plan[]" value="'.$pID.'" id="arm_user_suspended_plan_'.$pID.'"/>';
                                                
                                            }

                                            //if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                                                
                                                

                                                $arm_paid_post_plans_wrapper .= '<div style="position: relative;">';
                                                
                                                $arm_paid_post_plans_wrapper .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/grid_delete_icon_trans.png" class="arm_edit_plan_action_button armhelptip tipso_style" href="javascript:void(0)" title="'.__('Delete Post', 'ARMember').'" onclick="showConfirmBoxCallback_plan(\''.$pID.'\');">';
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box" id="arm_confirm_box_plan_'.$pID.'" style="right: -15px;top: 1.4rem;">';
                                                    
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_body">';
                                                
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_arrow"></div>';

                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_text">'.__('Are you sure you want to delete this post from user?', 'ARMember').'</div>'; 
                                                
                                                
                                                $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_btn_container">';
                                                $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armok arm_member_paid_plan_delete_btn" data-item_id='.$pID.' >'.__('Delete', 'ARMember').'</button>';

                                                $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">'.__('Cancel', 'ARMember').'</button>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div>';
                                                $arm_paid_post_plans_wrapper .= '</div></div>';
                                                
                                            //}
                                            

                                    $arm_paid_post_plans_wrapper .= '</td>';
                                $arm_paid_post_plans_wrapper .= '</tr>';


                                $count_plans++;
                            }
                            else{
                                if (!empty($pID)) {
                                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $pID, true);
                                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                    $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);



                                    if (!empty($planData)) {
                                        $planDetail = $planData['arm_current_plan_detail'];
                                        if (!empty($planDetail)) {
                                            $planObj = new ARM_Plan(0);
                                            $planObj->init((object) $planDetail);
                                        } else {
                                            $planObj = new ARM_Plan($pID);
                                        }
                                    }

                                    $no = $count_plans;
                                    $planName = $planObj->name;
                                    $grace_message = '';
                                    $starts_date = !empty($planData['arm_start_plan']) ? $planData['arm_start_plan'] : '';
                                    $started_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : '';

                                    $starts_on = !empty($starts_date) ? date_i18n($date_format, $starts_date) : '-';

                                    if($started_date != '' && $started_date <= $starts_date) {
                                        $starts_on = date_i18n($date_format, $started_date);
                                    }
                                    $expires_on = !empty($planData['arm_expire_plan']) ? '<span id="arm_user_expiry_date_' . $pID . '" style="display: inline;"> ' . date_i18n($date_format, $planData['arm_expire_plan']) . ' <img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . __('Change Expiry Date', 'ARMember') . '" data-plan_id="' . $pID . '" class="arm_edit_user_expiry_date armhelptip tipso_style"></span><span id="arm_user_expiry_date_box_' . $pID . '" style="display: none; position: relative; width: 157px;"><input type="text" value="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '"  data-date_format="'.$arm_common_date_format.'" name="arm_subscription_expiry_date_' . $pID . '"  id="arm_subscription_expiry_date_'.$pID.'_'.$user_id.'" class="arm_member_form_input arm_user_plan_expiry_date_picker" style="width: 120px; min-width: 120px;"/><img src="' . MEMBERSHIP_IMAGES_URL . '/arm_save_icon.png" style="vertical-align: middle;display:none;" width="14" height="16" title="' . __('Save Expiry Date', 'ARMember') . '" class="arm_edit_plan_action_button arm_member_save_post armhelptip tipso_style" id="arm_member_save_post_'.$pID.'" data-plan_id="' . $pID . '" data-user_id="' . $user_id . '" /><img src="' . MEMBERSHIP_IMAGES_URL . '/cancel_date_icon.png" width="11" height="11" title="' . __('Cancel', 'ARMember') . '" data-plan_id="' . $pID . '" data-plan-expire-date="' . date($arm_common_date_format, $planData['arm_expire_plan']) . '" class="arm_cancel_edit_user_expiry_date"></span>' : __('Never Expires', 'ARMember');

                                    $renewal_on = !empty($planData['arm_next_due_payment']) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';
                                    $trial_starts = !empty($planData['arm_trial_start']) ? $planData['arm_trial_start'] : '';
                                    $trial_ends = !empty($planData['arm_trial_end']) ? $planData['arm_trial_end'] : '';
                                    $arm_payment_mode = ( $planData['arm_payment_mode'] == 'auto_debit_subscription') ? '<br/>(' . __('Auto Debit','ARMember') . ')' : '';
                                    $arm_payment_cycle = !empty($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';
                                    if ($planObj->is_recurring()) {
                                        $recurring_plan_options = $planObj->prepare_recurring_data($arm_payment_cycle);
                                        $recurring_time = $recurring_plan_options['rec_time'];
                                        $completed = $planData['arm_completed_recurring'];
                                        if ($recurring_time == 'infinite' || empty($planData['arm_expire_plan'])) {
                                            $remaining_occurence = __('Never Expires', 'ARMember');
                                        } else {
                                            $remaining_occurence = $recurring_time - $completed;
                                        }

                                        if (!empty($planData['arm_expire_plan'])) {
                                            if ($remaining_occurence == 0) {
                                                $renewal_on = __('No cycles due', 'ARMember');
                                            } else {
                                                $renewal_on .= "<br/>( " . $remaining_occurence . __(' cycles due', 'ARMember') . " )";
                                            }
                                        }
                                        $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                                        $arm_grace_period_end = $planData['arm_grace_period_end'];

                                        if ($arm_is_user_in_grace == "1") {
                                            $arm_grace_period_end = date_i18n($date_format, $arm_grace_period_end);
                                            $grace_message .= "<br/>( " . __('grace period expires on', 'ARMember') ." ". $arm_grace_period_end . " )";
                                        }
                                    }

                                    $arm_plan_is_suspended = '';

                                    $trial_active = '';
                                    $plans_is_odd_even =($count_plans % 2 == 0) ? 'even' : 'odd';

                                    $arm_paid_post_plans_wrapper .= '<tr class="arm_user_plan_row '.$plans_is_odd_even.'" id="arm_user_future_plan_div_'.$count_plans.'">';
                                    $count_plans_no = $no + 1;
                                    //$arm_paid_post_plans_wrapper .= '<td>'.$count_plans_no.'</td>';

                                    $arm_paid_post_plans_wrapper .= '<td class="arm_edit_plan_name">'.$planName . $arm_plan_is_suspended.'</td>';
                                    $arm_paid_post_plans_wrapper .= '<td class="arm_edit_plan_type">'.$planObj->new_user_plan_text(false, $arm_payment_cycle).'</td>';
                                    $arm_paid_post_plans_wrapper .= '<td class="arm_edit_plan_start">'.$starts_on . $trial_active.'</td>';
                                    $arm_paid_post_plans_wrapper .= '<td class="arm_edit_plan_expiry">'.$expires_on.'</td>';
                                    $arm_paid_post_plans_wrapper .= '<td class="arm_edit_plan_cycle_date">'.$renewal_on . $grace_message . $arm_payment_mode.'</td>';

                                    $arm_paid_post_plans_wrapper .= '<td>';
                                    
                                    $arm_paid_post_plans_wrapper .= '<input name="arm_user_future_plan[]" value='.$pID.' type="hidden" id="arm_user_paid_post_future_plan_'.$pID.'">';

                                            $arm_paid_post_plans_wrapper .= '<div style="position: relative;">';
                                                
                                            $arm_paid_post_plans_wrapper .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/grid_delete_icon_trans.png" class="arm_edit_plan_action_button armhelptip tipso_style" href="javascript:void(0)" title="'.__('Delete Post', 'ARMember').'" onclick="showConfirmBoxCallback_plan(\''.$pID.'\');">';
                                            $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box" id="arm_confirm_box_plan_'.$pID.'" style="right: -15px;top: 1.4rem;">';
                                                
                                            $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_body">';
                                            
                                            $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_arrow"></div>';

                                            $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_text">'.__('Are you sure you want to delete this post from user?', 'ARMember').'</div>'; 
                                            
                                            
                                            $arm_paid_post_plans_wrapper .= '<div class="arm_confirm_box_btn_container">';
                                            $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armok arm_member_paid_plan_delete_btn" data-item_id='.$pID.' >'.__('Delete', 'ARMember').'</button>';

                                            $arm_paid_post_plans_wrapper .= '<button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">'.__('Cancel', 'ARMember').'</button>';
                                            $arm_paid_post_plans_wrapper .= '</div>';
                                            $arm_paid_post_plans_wrapper .= '</div>';
                                            $arm_paid_post_plans_wrapper .= '</div></div>';
                                               
                                           
                                            

                                        $arm_paid_post_plans_wrapper .=  '</td>';





                                    $arm_paid_post_plans_wrapper .= '</tr>';


                                    $count_plans++;
                                }
                            }
                            if( in_array( $pID, $futurePlanIDs ) ){
                                $arm_paid_post_plans_wrapper .= '<input name="arm_user_future_plan[]" value='.$pID.' type="hidden" id="arm_user_paid_post_future_plan_'.$uniq_delete_no.'">';
                            } else {
                                $arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_user_plan2[]" id="arm_user_paid_post_div_'.$uniq_delete_no.'" value="'.$pID.'"/>';
                                
                                $arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_subscription_start_date2[]" id="arm_user_paid_post_date_div_'.$uniq_delete_no.'" value='.date('m/d/Y', (int)$planData['arm_start_plan']).' />';
                            }
                            /*$arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_user_plan2[]" value="'.$pID.'"/>';
                            $arm_paid_post_plans_wrapper .= '<input type="hidden" name="arm_subscription_start_date2[]" value='.date('m/d/Y', $planData['arm_start_plan']).' />';*/
                        }
                    }
                } else{
                    $arm_paid_post_plans_wrapper .= '<tr class="arm_user_edit_plan_table" ><td colspan="6" style="text-align:center">'. __("This user don't have any paid post.", 'ARMember'). '</td></tr>';
                }


                $arm_paid_post_plans_wrapper .= '</table>';
                

                if(!empty($planIDs) && $membership_count>5){
                    $member_paid_post_plans_pagging = $arm_global_settings->arm_get_paging_links($current_page, $membership_count, $per_page);
                    $arm_paid_post_plans_wrapper .= '<div class="arm_member_paid_post_plans_pagination_block">';
                    $arm_paid_post_plans_wrapper .= '<div class="arm_member_paid_post_plans_modal_paging_container" style="float: right;">'.$member_paid_post_plans_pagging.'</div>';
                    $arm_paid_post_plans_wrapper .= '</div>';
                }
                 
                $arm_paid_post_plans_wrapper .= '</div>';
                $arm_paid_post_plans_wrapper .= '<input type="hidden" id="arm_paid_post_counter_value" value="'.count($planIDs).'">';
            }   
            return  $arm_paid_post_plans_wrapper;
        }
	
	    function arm_add_paid_post_message_types( $message_types ){

            $pp_message_types = array(
                'on_new_subscription_post' => esc_html__('On new paid post purchase', 'ARMember'),
                'on_renew_subscription_post' => esc_html__('On renew paid post purchase', 'ARMember'),
                'on_recurring_subscription_post' => esc_html__('On recurring paid post purchase', 'ARMember'),
                'on_cancel_subscription_post' => esc_html__( 'On cancel paid post', 'ARMember' ),
                'before_expire_post' => esc_html__('Before paid post expire', 'ARMember'),
                'on_expire_post' => esc_html__('On Expire paid post', 'ARMember')
            );

            return array_merge( $message_types, $pp_message_types );
        }

        function arm_update_paid_post_access_rules( $posted_data ){

            $form_data = isset( $posted_data['form_data'] ) ? json_decode( stripslashes_deep( $posted_data['form_data'] ), true ) : array();

            if( !empty( $form_data ) ){
                global $ARMember;
                foreach( $form_data as $rule_id => $rule_data ){
                    if( !empty( $rule_data['protection'] ) && 1 == $rule_data['protection'] ) {
                        $isEnablePaidPost = get_post_meta( $rule_id, 'arm_is_paid_post', true );
                        if( 1 == $isEnablePaidPost ){
                            $plan_id = $this->arm_get_plan_from_post_id( $rule_id );
                            if( !empty( $plan_id ) ){
                                $getRules = get_post_meta( $rule_id, 'arm_access_plan', false );
                                if( !empty( $getRules ) && !in_array( $plan_id, $getRules ) ){
                                    add_post_meta( $rule_id, 'arm_access_plan', $plan_id );
                                }
                            }
                        }
                    }
                }

            }

        }

        function arm_update_access_plan_for_drip_rules_callback( $post_id ){

            if( !empty( $post_id ) ){

                $plan_id = $this->arm_get_plan_from_post_id( $post_id );

                if( !empty( $plan_id ) ){
                    $getRules = get_post_meta( $post_id, 'arm_access_plan', false );
                    if( !empty( $getRules ) && !in_array( $plan_id, $getRules ) ){
                        add_post_meta( $post_id, 'arm_access_plan', $plan_id );
                    }
                }

            }
        }
	
	   function arm_ajax_display_paid_post_cycle() {
	        global $arm_payment_gateways, $ARMember, $arm_capabilities_global,$wpdb;
        
	        $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_pay_per_post'], '1');
	        $arm_currency = $arm_payment_gateways->arm_get_global_currency();
	        $type = 'failed';
	        $plan_name = '';
	        $content = '';
	        if( isset($_POST['paid_post_id']) && !empty($_POST['paid_post_id']) ) {
	            $count_cycle = '';
            
	            $paid_post_id = $_POST['paid_post_id'];
           
	            $paid_post_plan_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_post_id` = %d", $paid_post_id) );
            
	            $plan_name = esc_html(stripslashes($paid_post_plan_data->arm_subscription_plan_name));
	            $plan_options = isset( $paid_post_plan_data->arm_subscription_plan_options ) ? maybe_unserialize( $paid_post_plan_data->arm_subscription_plan_options ) : array();

	            if( isset( $plan_options['payment_cycles'] ) && !empty( $plan_options['payment_cycles'] ) ){
	                $payment_cycles_data = $plan_options['payment_cycles'];
            
	                if($payment_cycles_data > 0) {
	                    $type = 'success';
	                    $typeArrayMany = array(
	                        'D' => __("days", 'ARMember'),
	                        'W' => __("weeks", 'ARMember'),
	                        'M' => __("months", 'ARMember'),
	                        'Y' => __("years", 'ARMember'),
	                    );
	                    $typeArray = array(
	                        'D' => __("day", 'ARMember'),
	                        'W' => __("week", 'ARMember'),
	                        'M' => __("month", 'ARMember'),
	                        'Y' => __("year", 'ARMember'),
	                    );

	                    $content .= '<table class="arm_user_edit_plan_table" cellspacing="1" style="text-align: center; width:95%; border-left: 1px solid #eaeaea; margin: 20px; border-right: 1px solid #eaeaea;">';
	                    $content .= '<tr class="arm_user_plan_row arm_user_plan_head odd">';
	                    $content .= '<th class="arm_edit_plan_name">' . __('Label', 'ARMember') . '</th>';
	                    $content .= '<th class="arm_edit_plan_type">' . __('Amount', 'ARMember') . '</th>';
	                    $content .= '<th class="arm_edit_plan_start">' . __('Billing Cycle', 'ARMember') . '</th>';
	                    $content .= '<th class="arm_edit_plan_expire">' . __('Recurring Time', 'ARMember') . '</th>';
	                    $content .= '</tr>';

	                    foreach ($payment_cycles_data as $arm_cycle) {
	                        $count_cycle++;
	                        $row_class = ($count_cycle % 2 == 0) ? 'odd' : 'even';
	                        $arm_label = $arm_cycle['cycle_label'];
	                        $arm_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $arm_cycle['cycle_amount']) . ' ' . $arm_currency;
	                        $arm_billing_cycle = $arm_cycle['billing_cycle'];
	                        $arm_billing_type = $arm_cycle['billing_type'];
	                        $arm_recurring_time = $arm_cycle['recurring_time'];

	                        $arm_billing_text = '';
	                        if($arm_billing_cycle > 1) {
	                            $arm_billing_text = $arm_billing_cycle . ' ' . $typeArrayMany[$arm_billing_type];
	                        } else {
	                            $arm_billing_text = $arm_billing_cycle . ' ' . $typeArray[$arm_billing_type];
	                        }

	                        $content .= '<tr class="arm_user_plan_row arm_plan_cycle ' . $row_class . '">';
	                            $content .= '<td class="arm_edit_plan_name">' . $arm_label . '</td>';
	                            $content .= '<td class="arm_edit_plan_type">' . $arm_amount . '</td>';
	                            $content .= '<td class="arm_edit_plan_start">' . $arm_billing_text . '</td>';
	                            $content .= '<td class="arm_edit_plan_expire">' . $arm_recurring_time . '</td>';
	                        $content .= '</tr>';

	                    }

	                    $content .= '</table>';
	                }

	            } else {
	                $content = '<center>'.__('Plan does not have any cycle.', 'ARMember').'</center>';
	            }
	        } else {
	            $content = '<center>'.__('Plan does not have any cycle.', 'ARMember').'</center>';
	        }
	        echo $plan_name . '^|^' . $content;
	        die;
	   }

        function arm_get_total_members_in_paid_post($post_id = 0) {

           global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $res = 0;
            if (!empty($post_id)) {
                $user_arg = array(
                    'meta_key' => 'arm_user_post_ids',
                    'meta_value' => $post_id,
                    'meta_compare' => 'like',
                    'role__not_in' => 'administrator'
                );
                $users = get_users($user_arg);
                
                $res = 0;
                foreach ($users as $user) {
                    $post_ids = get_user_meta($user->ID, 'arm_user_post_ids', true);
                    if (!empty($post_ids) && is_array($post_ids)) {
                        if (in_array($post_id, $post_ids)) {
                            $res++;
                        }
                    }
                }
            }
            return $res;
        }

    }
}

global $arm_pay_per_post_feature;
$arm_pay_per_post_feature = new ARM_pay_per_post_feature();