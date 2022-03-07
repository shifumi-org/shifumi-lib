<?php
if (!class_exists('ARM_wocommerce_feature')) {

    class ARM_wocommerce_feature {

        var $isWocommerceFeature;

        function __construct() {
           
            $is_woocommerce_feature = get_option('arm_is_woocommerce_feature');
            $this->isWocommerceFeature = ($is_woocommerce_feature == '1') ? true : false;
            //add_action('admin_enqueue_scripts', array($this, 'arm_enqueue_woocommerce_stylesheet'));
            /* Update "arm_is_woocommerce_feature" option when woocommerce activation/deactivation */
            add_action('woocommerce_installed', array($this, 'arm_woomcommerce_activation'));
            add_action('deactivated_plugin', array($this, 'arm_woomcommerce_deactivation'), 10, 2);
            
            /* Restrict product for woocommerce */
            if ($this->isWocommerceFeature) {
                /* To Add Woocommerce in payment gateway array */
                add_filter('arm_filter_gateway_names', array($this, 'arm_woocommerce_add_payment_gateway_name'), 10, 1);
                add_filter('arm_get_payment_gateways_in_filters', array($this, 'arm_woocommerce_add_payment_gateway'), 10, 1);
                
                add_action('arm_update_feature_settings', array($this, 'arm_woocommerece_update_feature_seetings'), 10, 1);
                add_filter('arm_add_currency_in_default_list', array($this, 'arm_woocommerce_add_currency'));

                /* To add ARMember Plan tab in product data metabox of woocommerce */
                
                add_action('woocommerce_product_write_panel_tabs', array($this, 'arm_woocommerce_armember_plan_tab')); //3.0.2 - 3.0.6

                /* To add ARmember Plans Dropdown in ARMember Plan tab in product data metabox of woocommerce */
                add_action('woocommerce_product_data_panels', array($this, 'arm_woocommerce_armember_plan_tab_options')); //3.0.2 - 3.0.6

                add_action('wp_ajax_woocommerce_get_plan_cycle', array($this, 'arm_woocommerce_plan_cycle_func'));

                /* To save data of ARmember Plans Dropdown in ARMember Plan tab in product data metabox of woocommerce */
                add_action('woocommerce_process_product_meta', array($this, 'arm_woocommerce_process_armember_plan_tab_meta')); //3.0.2

                /* To make cart empty when add To cart button clicked in front end */
                add_filter('woocommerce_add_cart_item_data', array($this, 'arm_woocommerce_empty_then_add_to_cart'), 10, 3); //3.0.2 - 3.0.6

                /* To remove Quantity change option in cart in front end */
                add_filter('woocommerce_is_sold_individually', array($this, 'arm_woocommerce_remove_all_quantity_fields'), 10, 2); //3.0.2

                /* To add product id as a order meta when ordered is placed */
                add_action('woocommerce_checkout_update_order_meta', array($this, 'arm_woocommerce_update_order_meta')); //3.0.2 - 3.0.6

                /* Process when order status is either refunded, failed, on_hold */
                add_action("woocommerce_order_status_refunded", array($this, 'arm_woocommerce_cancel_membership_from_order'));
                add_action("woocommerce_order_status_failed", array($this, 'arm_woocommerce_cancel_membership_from_order'));
                add_action("woocommerce_order_status_cancelled", array($this, 'arm_woocommerce_cancel_membership_from_order'));

                /* Assign plan to registered order owner when order is completed */
                add_action("woocommerce_order_status_completed", array($this, 'arm_woocommerce_add_member')); 
                                
                /* Set order status by default to complete if product is virtual, Here all products will be virtual */
                add_filter( 'woocommerce_payment_complete_order_status', array($this, 'arm_woocommerce_make_order_status_complete_for_virtual_products'), 10, 2); //3.0.2 -3.0.6
                
                add_action('woocommerce_checkout_order_processed', array($this, 'arm_woocommerce_after_checkout_validation'), 50, 2); //3.0.2-3.0.6

                // Woocommerce Payment Gateway Hooks

                //Filter for display woocommerce option in payment gateway section.
                add_filter('arm_get_payment_gateways', array($this, 'arm_woocommerce_add_payment_gateway'));

                //Filter for submit form details and add product to cart
                add_action('arm_payment_gateway_validation_from_setup', array($this, 'arm2_payment_gateway_form_submit_action'), 10, 4);

                //Action for create woocommerce products after add/update setup details.
                add_action('arm_saved_membership_setup', array($this, 'arm_setup_plans_create_product'), 10, 2);

                //Woocommerce hook for modify cart price
                add_filter('woocommerce_before_calculate_totals', array($this, 'arm_modify_cart_price'), 10, 1);

                //Woocommerce hook for remove cart id from entry table when product remove from cart
                add_action('woocommerce_cart_item_removed', array($this, 'arm_remove_entry_cart_id'), 10, 2);

                //For remove auto-debit option from admin and front side.
                add_filter('arm_not_display_payment_mode_setup', array($this, 'arm_not_display_payment_mode_setup_func'), 10, 1);

                add_action('woocommerce_checkout_order_processed', array($this, 'arm_modify_order_meta_for_woocommerce_payment_gateway'), 10, 3);
            }
        }

        function arm_modify_order_meta_for_woocommerce_payment_gateway($order_id, $posted_data, $order){
            global $wpdb, $woocommerce, $ARMember;
            $arm_woocommerce_cart_key = "";
            foreach ($woocommerce->cart->get_cart() as $wc_key => $wc_item) {
                $arm_woocommerce_cart_key = $wc_item['key'];
            }

            if(!empty($arm_woocommerce_cart_key)){
                $arm_entry_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_value` LIKE '%".$arm_woocommerce_cart_key."%' ORDER BY arm_entry_id DESC", ARRAY_A);
                if(!empty($arm_entry_data)){

                    do_action('arm_woocommerce_add_woocommerce_meta_from_outside', $arm_entry_data, $order_id, $order, $posted_data);

                    if($arm_entry_data['arm_is_post_entry'] == 1 && !empty($arm_entry_data['arm_paid_post_id']))
                    {
                        $arm_plan_id = $arm_entry_data['arm_plan_id'];

                        //Update paid post id in product meta
                        /*
                        $arm_woo_order_obj = wc_get_order($order_id);
                        $arm_woo_order_items = $order->get_items();
                        foreach($arm_woo_order_items as $item_key => $item_val){
                             $product_id = $item_val->get_product_id();
                             update_post_meta($product_id, '_arm_woocommerce_membership_post', $arm_plan_id);
                        }
                        */
                        $arm_user_id = get_current_user_id();
                        if(!empty($arm_plan_id)){
                            $arm_paid_post_meta_value[] = $arm_plan_id;
                            update_post_meta($order_id, 'arm_mapped_order_product_post', maybe_serialize($arm_paid_post_meta_value));
                        }
                        $arm_entry_value = maybe_unserialize($arm_entry_data['arm_entry_value']);
                        $arm_selected_plan_cycle = $arm_entry_value['arm_selected_payment_cycle'];
                        update_post_meta($order_id, 'arm_woo_payment_post_selected_cycle', $arm_selected_plan_cycle);
                    }
                    else
                    {
                        $arm_plan_id[] = $arm_entry_data['arm_plan_id'];
                        update_post_meta($order_id, 'arm_mapped_order_product_plans', maybe_serialize($arm_plan_id));
                        $arm_entry_value = maybe_unserialize($arm_entry_data['arm_entry_value']);
                        $arm_selected_plan_cycle = $arm_entry_value['arm_selected_payment_cycle'];
                        update_post_meta($order_id, 'arm_woo_payment_selected_cycle', $arm_selected_plan_cycle);
                    }
                }
            }
        }

        function arm_not_display_payment_mode_setup_func($gateway_name_arr){
            $gateway_name_arr[] = 'woocommerce';
            return $gateway_name_arr;
        }

        function arm_remove_entry_cart_id($cart_item_key, $instance){
            global $wpdb, $woocommerce, $ARMember;
            if(!empty($instance) && !empty($cart_item_key)){
                $arm_entry_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_value` LIKE '%".$arm_woocommerce_cart_key."%' ORDER BY arm_entry_id DESC", ARRAY_A);
                if(!empty($arm_entry_data)){
                    $arm_entry_id = $arm_entry_data['arm_entry_id'];
                    $arm_entry_value = maybe_unserialize($arm_entry_data['arm_entry_value']);
                    $arm_entry_value['arm_woocommerce_gateway_cart_key'] = '';
                    unset($arm_entry_value['arm_woocommerce_gateway_cart_key']);
                    $arm_entry_value = maybe_serialize($arm_entry_value);

                    $arm_entry_update_data = array( 'arm_entry_value' => $arm_entry_value );
                    $arm_entry_update_where_condition = array( 'arm_entry_id' => $arm_entry_id );
                    $arm_update_entry_data = $wpdb->update($ARMember->tbl_arm_entries, $arm_entry_update_data, $arm_entry_update_where_condition);
                }
            }
        }

        function arm_modify_cart_price($cart_obj){
            global $wpdb, $woocommerce, $ARMember, $arm_payment_gateways;

            $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
            $payment_gateway_options = isset($all_payment_gateways['woocommerce'] ) ? $all_payment_gateways['woocommerce'] : array();
            if(!empty($payment_gateway_options)) {
                foreach ($woocommerce->cart->get_cart() as $wc_key => $wc_item) {
                    $arm_woocommerce_cart_key = $wc_item['key'];
                    if(!empty($arm_woocommerce_cart_key)){
                        $arm_entry_data = $wpdb->get_row("SELECT * FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_value` LIKE '%".$arm_woocommerce_cart_key."%' ORDER BY arm_entry_id DESC", ARRAY_A);
                        if(!empty($arm_entry_data)){
                            $arm_entry_id = $arm_entry_data['arm_entry_id'];
                            $arm_plan_id = $arm_entry_data['arm_plan_id'];
                            $arm_entry_value = maybe_unserialize($arm_entry_data['arm_entry_value']);

                            $arm_return_data = array();
                            $arm_return_data = apply_filters('arm_calculate_payment_gateway_submit_data', $arm_return_data, 'woocommerce', $payment_gateway_options, $arm_entry_value, $arm_entry_id);
                            if(!empty($arm_return_data))
                            {
                                $arm_total_payable_amount = !empty($arm_return_data['arm_payable_amount']) ? $arm_return_data['arm_payable_amount'] : 0;

                                if(!empty($arm_return_data['arm_recurring_data']['trial']))
                                {
                                    //If trial enable then enable it for subscription.
                                    $arm_total_payable_amount = number_format((float)$arm_return_data['arm_recurring_data']['trial']['amount'], 2);
                                }
                                
                                $wc_item['data']->set_price($arm_total_payable_amount);

                                if(!empty($arm_plan_id)){
                                    $arm_plan_name = $arm_return_data['arm_plan_obj']->name;
                                    $wc_item['data']->set_name($arm_plan_name);
                                }
                            }
                        }
                    }
                }
            }
        }

        function arm_setup_plans_create_product($setup_id, $db_data){
            global $wpdb, $ARMember, $woocommerce;
            if($this->isWocommerceFeature && is_plugin_active('woocommerce/woocommerce.php')){
                $arm_setup_modules = maybe_unserialize($db_data['arm_setup_modules']);
                if(!empty($arm_setup_modules['modules']['gateways']) && in_array('woocommerce', $arm_setup_modules['modules']['gateways'])){
                    $arm_setup_plans = $arm_setup_modules['modules']['plans'];

                    $arm_created_product_ids = array();

                    $arm_woocommerce_product_exist = $this->arm2_woo_product_find_product();
                    if(empty($arm_woocommerce_product_exist)){
                        $this->arm_create_woocommerce_product();
                    }
                }
            }
        }

        function arm_create_woocommerce_product(){
            $arm_product_name = __('Membership Product', 'ARMember');

            $arm_product_array = array(
                'post_author'  => get_current_user(),
                'post_title'  => $arm_product_name,
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'product',
            ); 

            $arm_product_id = wp_insert_post($arm_product_array);

            wp_set_object_terms($arm_product_id, 'simple', 'product_type');
            wp_set_object_terms($arm_product_id, ['exclude-from-catalog', 'exclude-from-search'], 'product_visibility');
            update_post_meta($arm_product_id, '_visibility', 'hidden');
            update_post_meta($arm_product_id, '_stock_status', 'instock');
            update_post_meta($arm_product_id, 'total_sales', '0');
            update_post_meta($arm_product_id, '_downloadable', 'no');
            update_post_meta($arm_product_id, '_virtual', 'yes' );
            update_post_meta($arm_product_id, '_price', 0);
            update_post_meta($arm_product_id, '_regular_price', 0);
            update_post_meta($arm_product_id, '_sale_price', '');
            update_post_meta($arm_product_id, '_featured', 'no');
            update_post_meta($arm_product_id, '_weight', '');
            update_post_meta($arm_product_id, '_length', '');
            update_post_meta($arm_product_id, '_width', '');
            update_post_meta($arm_product_id, '_height', '');
            update_post_meta($arm_product_id, '_sku', '');
            update_post_meta($arm_product_id, '_product_attributes', array());
            update_post_meta($arm_product_id, '_sale_price_dates_from', '');
            update_post_meta($arm_product_id, '_sale_price_dates_to', '');
            update_post_meta($arm_product_id, '_sold_individually', 'yes');
            update_post_meta($arm_product_id, '_manage_stock', 'no');
            update_post_meta($arm_product_id, '_backorders', 'no');
            update_post_meta($arm_product_id, '_stock', '');
            update_post_meta($arm_product_id, '_arm_woocommerce_membership_product', '1');
        }

        function arm2_woo_product_find_product(){
            $arm_find_plan_exist_product_args = array(
                'post_type'  => 'product',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => '_arm_woocommerce_membership_product',
                        'value' => '1'
                    )
                ),
            );

            $arm_find_plan_exist_product_qry = new WP_Query($arm_find_plan_exist_product_args);

            $arm_find_plan_exist_product = $arm_find_plan_exist_product_qry->have_posts();

            $arm_existing_product_id = "";
            if($arm_find_plan_exist_product){
                $arm_find_plan_exist_product_qry->the_post();
                $arm_existing_product_id = $arm_find_plan_exist_product_qry->post->ID;
            }

            return $arm_existing_product_id;
        }

        function arm2_payment_gateway_form_submit_action($payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0){
            global $wpdb, $ARMember, $woocommerce, $arm_global_settings, $arm_member_forms, $arm_payment_gateways;
            if($payment_gateway == "woocommerce" && $this->isWocommerceFeature && is_plugin_active('woocommerce/woocommerce.php')){
                $arm_entry_id = $posted_data['arm_entry_id'];
                $arm_plan_id = !empty($posted_data['subscription_plan']) ? $posted_data['subscription_plan'] : 0;
                $arm_plan_obj = new ARM_Plan($arm_plan_id);
                $arm_is_recurring = $arm_plan_obj->is_recurring();

                $arm_product_id = 0;

                $arm_product_id = $this->arm2_woo_product_find_product();

                if(!empty($arm_product_id))
                {
                    $arm_post_status = get_post_status($arm_product_id);
                    if($arm_post_status != "publish"){
                        $arm_product_id = 0;
                    }
                
                    //If user is not registered then first register and then starts session.
                    //----------------------------------------------------
                        $arm_user_id = 0;
                        $arm_user_login = !empty($posted_data['user_login']) ? $posted_data['user_login'] : '';
                        if(!empty($arm_user_login) && !username_exists($arm_user_login)){
                            $entry_data = $wpdb->get_row("SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `" . $ARMember->tbl_arm_entries . "` WHERE `arm_entry_id`='" . $arm_entry_id . "'", ARRAY_A);
                            $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                            if(!empty($entry_values['subscription_plan'])){
                                unset($entry_values['subscription_plan']);
                            }

                            if(!empty($entry_values['_subscription_plan'])){
                                unset($entry_values['_subscription_plan']);
                            }
                            $setup_id = $entry_values['setup_id'];
                            $form_id = $entry_data['arm_form_id'];
                            $armform = new ARM_Form('id', $form_id);
                            if(in_array($armform->type, array('registration'))){
                                $arm_user_id = $arm_member_forms->arm_register_new_member($entry_values, $armform);
                            }
                        }else{
                            if(is_user_logged_in()){
                                $arm_user_id = get_current_user_id();
                            }else{
                                $arm_user_obj = get_user_by('login', $arm_user_login);
                                $arm_user_id = $arm_user_obj->ID;
                            }
                        }
                    //----------------------------------------------------

                    update_user_meta($arm_user_id, 'arm_wooc_gateway_entry_id', $arm_entry_id);  

                    //If tax applied then store applied tax_amount for that entry id
                    if(!empty($posted_data['arm_common_tax_amount'])){
                        update_user_meta($arm_user_id, 'arm_wooc_gateway_tax_'.$arm_entry_id, $posted_data['arm_common_tax_amount']);
                    }

                    //If coupon applied then store applied coupon data for that entry id
                    $arm_applied_coupon_code = !empty($posted_data['arm_coupon_code']) ? $posted_data['arm_coupon_code'] : '';
                    if(!empty($arm_applied_coupon_code)){
                        update_user_meta($arm_user_id, 'arm_wooc_gateway_coupon_'.$arm_entry_id, $arm_applied_coupon_code);
                    }

                    //After get product ID, now add product to woocommerce cart if not exist into cart
                    $woocommerce->cart->empty_cart();
                    $woocommerce->cart->add_to_cart($arm_product_id);

                    //Get cart Key for store into entries table.
                    $arm_woocommerce_cart_obj = $woocommerce->cart->get_cart();
                    $arm_woocommerce_cart_key = '';
                    foreach($arm_woocommerce_cart_obj as $arm_woo_key => $arm_woo_val){
                        $arm_woocommerce_cart_key = $arm_woo_val['key'];
                    }

                    //Save woocommerce cart key in entry table
                    $arm_get_entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($arm_entry_id);
                    $arm_entry_value = maybe_unserialize($arm_get_entry_data['arm_entry_value']);
                    $arm_entry_value['arm_woocommerce_gateway_cart_key'] = $arm_woocommerce_cart_key;

                    $arm_entry_value = maybe_serialize($arm_entry_value);
                    $arm_entry_update_data = array( 'arm_entry_value' => $arm_entry_value );
                    $arm_entry_update_where_condition = array( 'arm_entry_id' => $arm_entry_id );
                    $arm_update_entry_data = $wpdb->update($ARMember->tbl_arm_entries, $arm_entry_update_data, $arm_entry_update_where_condition);

                    //Get woocommerce checkout URL.
                    $arm_woo_checkout_url = wc_get_checkout_url();

                    $arm_woo_redirect_checkout = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $arm_woo_checkout_url . '";</script>';
                    $return = array('status' => 'success', 'type' => 'redirect', 'message' => $arm_woo_redirect_checkout);
                    echo json_encode($return);
                    die;
                }
                else
                {
                    $err_msg = (!empty($err_msg)) ? $err_msg : __('Sorry, No Woocommerce product found for selected plan', 'ARMember');
                    $err_msg = '<div class="arm_error_msg"><ul><li>' . $err_msg . '</li></ul></div>';
                    $return = array('status' => 'error', 'type' => 'message', 'message' => $err_msg);
                    echo json_encode($return);
                    die;
                }
            }
        }

        function arm_woocommerce_exclude_restrict_item_for_widget($query_args){
            global $wp, $wpdb, $ARMember, $arm_access_rules;

            $arm_default_access_rules = $arm_access_rules->arm_get_default_access_rules();
                        $arm_allow_content_listing = isset($arm_default_access_rules['arm_allow_content_listing']) ? $arm_default_access_rules['arm_allow_content_listing'] : 0;

            if (!is_admin() && !current_user_can('administrator') && $arm_allow_content_listing != 1) {
                if (is_user_logged_in()) {
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
                }
                else
                {
                    $current_user_plan = array();
                }

                $rargs = array(
                        'meta_key' => 'arm_access_plan',
                        'meta_value' => '0',
                        'post_status' => 'publish',
                        'post_type' => 'product'
                );

                $result_pages = get_posts($rargs);

                
                $restrict_posts =  array(); 
                if (!empty($result_pages)) {
                        foreach ($result_pages as $rp) {
                                $obj_plans = get_post_meta($rp->ID, 'arm_access_plan');
                                $obj_plans = !empty($obj_plans) ? $obj_plans : array();
                                $obj_plans_array = array_intersect($current_user_plan, $obj_plans);
                                if (empty($obj_plans_array)) {
                                        $restrict_posts[] = $rp->ID;
                                }
                        }
                }                
                
                if(!empty($restrict_posts)){
                    $query_args['post__not_in'] = $restrict_posts;
                }

            }


            
            return $query_args;
        }
        
        function arm_woocommerce_exclude_restrict_item($query_args, $atts, $loop_name) {
            
            global $wp, $wpdb, $ARMember;
           
            if (!is_admin() && !current_user_can('administrator')) {
                if (is_user_logged_in()) {
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
                }
                else
                {
                    $current_user_plan = array();
                }

                $rargs = array(
                        'meta_key' => 'arm_access_plan',
                        'meta_value' => '0',
                        'post_status' => 'publish',
                        'post_type' => 'product'
                );

                $result_pages = get_posts($rargs);

                

                if (!empty($result_pages)) {
                        foreach ($result_pages as $rp) {
                                $obj_plans = get_post_meta($rp->ID, 'arm_access_plan');
                                $obj_plans = !empty($obj_plans) ? $obj_plans : array();
                                $obj_plans_array = array_intersect($current_user_plan, $obj_plans);
                                if (empty($obj_plans_array)) {
                                        $restrict_posts[] = $rp->ID;
                                }
                        }
                }

                if(!empty($restrict_posts) && !empty($query_args['post__in'])) {
                    foreach($restrict_posts as $rid){
                        if(in_array($rid, $query_args['post__in'])){
                            unset($query_args['post__in'][array_search($rid, $query_args['post__in'])]);
                        }
                    }
                }
                
               
                
                $query_args['post__not_in'] = $restrict_posts;
            }
            
            return $query_args;

        }
        
        function arm_woocommerce_after_checkout_validation( $order_id, $woo_posted_data ) {
            global $wpdb, $ARMember, $arm_pay_per_post_feature;
            
            $entry_email = $woo_posted_data['billing_email'];
            $setup_name = 'woocommerce';
            $ip_address = $ARMember->arm_get_ip_address();
            $description = maybe_serialize(array('browser' => $_SERVER['HTTP_USER_AGENT'], 'http_referrer' => @$_SERVER['HTTP_REFERER']));
            $form_id = $order_id;
            $user_id = '0';
            $plan_id = '0';
            $entry_post_data = apply_filters('arm_add_arm_entries_value', $woo_posted_data);
            
            $user_info = wp_get_current_user();
            $user_id = $user_info->ID;

            $arm_is_post_entry = 0;
            $arm_paid_post_id = 0;
            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                $arm_is_post_entry = 1;
                $arm_paid_post_id = $plan_id;
                //$plan_id = 0;
            }

            $new_entry = array(
                'arm_entry_email' => $entry_email,
                'arm_name' => $setup_name,
                'arm_description' => $description,
                'arm_ip_address' => $ip_address,
                'arm_browser_info' => $_SERVER['HTTP_USER_AGENT'],
                'arm_entry_value' => maybe_serialize($entry_post_data),
                'arm_form_id' => $form_id,
                'arm_user_id' => $user_id,
                'arm_plan_id' => $plan_id,
                'arm_is_post_entry' => $arm_is_post_entry,
                'arm_paid_post_id' => $arm_paid_post_id,
                'arm_created_date' => date('Y-m-d H:i:s')
            );
            $new_entry_results = $wpdb->insert($ARMember->tbl_arm_entries, $new_entry);
        }
              
        function arm_enqueue_woocommerce_stylesheet()
        {
            global $post_type;
            if((isset($_GET['post'])) || (isset($_GET['page']) && ($_GET['page'] == 'arm_feature_settings')) || (isset($_GET['post_type']) && $_GET['post_type'] == 'product' ))
            {
                wp_enqueue_style('arm_woocommerce_css', MEMBERSHIP_URL . '/css/arm_woocommerce.css', array(), MEMBERSHIP_VERSION);

                wp_enqueue_script('arm_woocommerce_js', MEMBERSHIP_URL . '/js/arm_woocommerce.js', array(), MEMBERSHIP_VERSION);
            }
        }
        
        function arm_woocommerece_update_feature_seetings($posted_data)
        {
              
            global $wp, $wpdb, $wp_rewrite, $ARMember;
            $features_options = $posted_data['arm_features_options'];
            $arm_features_status = (!empty($posted_data['arm_features_status'])) ? $posted_data['arm_features_status'] : 0;
            if ($features_options == 'arm_is_woocommerce_feature') 
            {
                if (file_exists(WP_PLUGIN_DIR . "/woocommerce/woocommerce.php")) 
                {
                    if (is_plugin_active('woocommerce/woocommerce.php')) 
                    {
                        update_option($features_options, $arm_features_status);
                        update_option('arm_is_woocommerce_feature_old', $arm_features_status);
                        $response = array('type' => 'success', 'msg' => __('Features Settings Updated Successfully.', 'ARMember'));
                        echo json_encode($response);
                        die();
                    } 
                    else 
                    {
                        update_option($features_options, 0);
                        $response = array('type' => 'wocommerce_error', 'msg' => __('Please activate Woocommerce and try to active this add-on.', 'ARMember'));
                        echo json_encode($response);
                        die();
                    }
                } 
                else 
                {
                    update_option($features_options, 0);
                    $response = array('type' => 'wocommerce_error', 'msg' => __('Please install Woocommerce and try to active this add-on.', 'ARMember'));
                    echo json_encode($response);
                    die();
                }
            }
        }
        
        function arm_woomcommerce_deactivation($plugin, $network_activation) {
            if ($plugin == "woocommerce/woocommerce.php") {
                update_option('arm_is_woocommerce_feature', 0);
            }
        }

        function arm_woomcommerce_activation() {
            $arm_is_woocommerce_feature_old = get_option('arm_is_woocommerce_feature_old');
            update_option('arm_is_woocommerce_feature', $arm_is_woocommerce_feature_old);
        }

        function arm_woocommerce_armember_plan_tab() {
            global $arm_pay_per_post_feature;
            ?>
                <li class="arm_plan_mapping_tab"><a href="#arm_woocommerce_plan_mapping_data_tab"><span class="arm_plan_map_span"><?php _e('ARMember Plan Selection', 'ARMember'); ?></span></a></li>

            <?php
                if($arm_pay_per_post_feature->isPayPerPostFeature)
                {
            ?>
                    <li class="arm_post_mapping_tab"><a href="#arm_woocommerce_post_mapping_data_tab"><span class="arm_post_map_span"><?php _e('ARMember Paid Post Selection', 'ARMember'); ?></span></a></li>
            <?php
                }
        }

        function arm_woocommerce_armember_plan_tab_options() {
            global $arm_subscription_plans, $post;

            $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('all');

            $arm_membership_plan_options[0] = 'None';

            foreach ($all_plans as $plan) {
                $key = $plan['arm_subscription_plan_id'];
                $arm_membership_plan_options[$key] = $plan['arm_subscription_plan_name'];
            }
            ?>
            <div id="arm_woocommerce_plan_mapping_data_tab" class="panel woocommerce_options_panel">
                <div class="options_group">
                    <p class="form-field">
                        <img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img arm_woo_addon_loader_img" width="22" height="22" />
                        <?php
                        $arm_woo_sel_plan_id = get_post_meta($post->ID, '_arm_woocommerce_membership_plan', true);
                        woocommerce_wp_select(
                            array(
                                'id' => '_armember_plan_select',
                                'label' => __('Assign ARMember Plan to this Product', 'ARMember'),
                                'options' => $arm_membership_plan_options,
                                'value' => $arm_woo_sel_plan_id,
                            )
                        );
                        $arm_woo_sel_plan_cycle_id = "";
                        if($arm_woo_sel_plan_id != "") {
                            $arm_woo_sel_plan = new ARM_Plan($arm_woo_sel_plan_id);
                            if($arm_woo_sel_plan->is_recurring()) {
                                $arm_woo_row_is_visible = "block";
                                $arm_woo_sel_plan_cycle_id = get_post_meta($post->ID, '_arm_woocommerce_membership_plan_subscription_id', true);

                                $woo_sel_plan_cycles = $arm_woo_sel_plan->options['payment_cycles'];
                                $woo_sel_plan_cycles_arr = array();
                                foreach ($woo_sel_plan_cycles as $cycle_key => $p) {
                                    $woo_sel_plan_cycles_arr[$cycle_key] = $p['cycle_label'];
                                }
                            }
                            else {
                                $arm_woo_row_is_visible = "none";
                            }
                        }
                        else {
                            $arm_woo_row_is_visible = "none";
                        }
                        ?>
                    </p>

                    <div class="form-field arm_woocommerce_selected_plan_cycle" style="display: <?php echo $arm_woo_row_is_visible?>;">
                        <?php
                            if($arm_woo_row_is_visible == "block") {
                                woocommerce_wp_select(
                                    array(
                                        'id' => '_armember_plan_cycle',
                                        'label' => __('Select Payment Cycle', 'ARMember'),
                                        'options' => $woo_sel_plan_cycles_arr,
                                        'value' => $arm_woo_sel_plan_cycle_id,
                                    )
                                );
                            }
                        ?>
                    </div>
                    <p>
                        <span class="arm_map_plan_description"><?php _e('Please note that when user will purchase this product, mapped ARMember plan will be assigned to that user.','ARMember'); ?></span>
                        <br/>
                        <span class="arm_map_plan_description"><?php _e('If user has already one ARMember plan, then it would be updated when he will purchase this product.','ARMember'); ?></span>
                        <br/>
                        <span class="arm_color_red"><?php _e('Important Note:','ARMember'); ?></span>
                        <br/>  
                        <span class="arm_map_plan_description"><?php _e('If you will select any plan which is having "subscription/recurring payment" type, then it will be considered as "semi automatic subscription" always.','ARMember'); ?></span>
                        <br/>
                        <span class="arm_map_plan_description"><?php _e('To assign ARMember Plan to this product, please mark product as Virtual, otherwise order status of this product wont be autocomplete.','ARMember'); ?></span>
                    </p>
                </div>   
            </div>
            <?php
                $arm_membership_plan_options = array();
                $all_posts = $arm_subscription_plans->arm_get_paid_post_data('all');
                $arm_membership_plan_options[0] = 'None';
                if(!empty($all_posts)){
                    foreach ($all_posts as $plan) {
                        $key = $plan['arm_subscription_plan_id'];
                        $arm_membership_plan_options[$key] = $plan['arm_subscription_plan_name'];
                    }
                }
            ?>
            <div id="arm_woocommerce_post_mapping_data_tab" class="panel woocommerce_options_panel _armember_post_select_field">
                <div class="options_group">
                    <p class="form-field">
                        <img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' ?>" class="arm_addon_loader_img arm_post_woo_addon_loader_img" width="22" height="22" />
                        <?php
                        $arm_woo_sel_plan_id = get_post_meta($post->ID, '_arm_woocommerce_membership_post', true);
                        woocommerce_wp_select(
                            array(
                                'id' => '_armember_post_select',
                                'label' => __('Assign ARMember Paid Post to this Product', 'ARMember'),
                                'options' => $arm_membership_plan_options,
                                'value' => $arm_woo_sel_plan_id,
                            )
                        );
                        $arm_woo_sel_plan_cycle_id = "";
                        if($arm_woo_sel_plan_id != "") {
                            $arm_woo_sel_plan = new ARM_Plan($arm_woo_sel_plan_id);
                            if($arm_woo_sel_plan->is_recurring()) {
                                $arm_woo_row_is_visible = "block";
                                $arm_woo_sel_plan_cycle_id = get_post_meta($post->ID, '_arm_woocommerce_membership_post_subscription_id', true);

                                $woo_sel_plan_cycles = $arm_woo_sel_plan->options['payment_cycles'];
                                $woo_sel_plan_cycles_arr = array();
                                foreach ($woo_sel_plan_cycles as $cycle_key => $p) {
                                    $woo_sel_plan_cycles_arr[$cycle_key] = $p['cycle_label'];
                                }
                            }
                            else {
                                $arm_woo_row_is_visible = "none";
                            }
                        }
                        else {
                            $arm_woo_row_is_visible = "none";
                        }
                        ?>
                    </p>

                    <div class="form-field arm_woocommerce_selected_post_cycle" style="display: <?php echo $arm_woo_row_is_visible?>;">
                        <?php
                            if($arm_woo_row_is_visible == "block") {
                                woocommerce_wp_select(
                                    array(
                                        'id' => '_armember_post_cycle',
                                        'label' => __('Select Payment Cycle', 'ARMember'),
                                        'options' => $woo_sel_plan_cycles_arr,
                                        'value' => $arm_woo_sel_plan_cycle_id,
                                    )
                                );
                            }
                        ?>
                    </div>
                    <p>
                        <span class="arm_map_plan_description"><?php _e('Please note that when user will purchase this product, mapped ARMember paid post will be assigned to that user.','ARMember'); ?></span>
                        <br/>
                        <span class="arm_map_plan_description"><?php _e('If user has already one ARMember paid post, then it would be updated when he will purchase this product.','ARMember'); ?></span>
                        <br/>
                        <span class="arm_color_red"><?php _e('Important Note:','ARMember'); ?></span>
                        <br/>  
                        <span class="arm_map_plan_description"><?php _e('If you will select any paid post which is having "subscription/recurring payment" type, then it will be considered as "semi automatic subscription" always.','ARMember'); ?></span>
                        <br/>
                        <span class="arm_map_plan_description"><?php _e('To assign ARMember Paid Post to this product, please mark product as Virtual, otherwise order status of this product wont be autocomplete.','ARMember'); ?></span>
                    </p>
                </div>   
            </div>
        <?php
        }

        function arm_woocommerce_plan_cycle_func() {

            $plan = new ARM_Plan($_GET["plan_id"]);

            if($plan->is_recurring()) {
                $plan_cycles = $plan->options['payment_cycles'];
                $plan_cycles_arr = array();

                foreach ($plan_cycles as $cycle_key => $p) {
                    $plan_cycles_arr[$cycle_key] = $p['cycle_label'];
                }

                woocommerce_wp_select (
                    array (
                        'id' => '_armember_plan_cycle',
                        'label' => __('Select Payment Cycle', 'ARMember'),
                        'options' => $plan_cycles_arr,
                        'value' => "0",
                    )
                );
            }
            else {
                echo "null";
            }

            exit;
        }

        function arm_woocommerce_process_armember_plan_tab_meta($post_id) {
            global $arm_pay_per_post_feature;

            // Save ARMember Plans
            $plan = $_POST['_armember_plan_select'];

            if (isset($plan)) {
                update_post_meta($post_id, '_arm_woocommerce_membership_plan', esc_attr($plan));
            }

            if(isset($_POST['_armember_plan_cycle'])) {
                update_post_meta($post_id, '_arm_woocommerce_membership_plan_subscription_id', esc_attr($_POST['_armember_plan_cycle']));
            }


            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                $plan = $_POST['_armember_post_select'];

                if (isset($plan)) {
                    update_post_meta($post_id, '_arm_woocommerce_membership_post', esc_attr($plan));
                }

                if(isset($_POST['_armember_post_cycle'])) {
                    update_post_meta($post_id, '_arm_woocommerce_membership_post_subscription_id', esc_attr($_POST['_armember_post_cycle']));
                }
            }
        }

        function arm_woocommerce_empty_then_add_to_cart($cart_item_data, $product_id, $variation_id) {
            global $woocommerce, $is_multiple_membership_feature, $arm_pay_per_post_feature;

            if(!$is_multiple_membership_feature->isMultipleMembershipFeature){
                $mapped_product_id_array = array();
                $arm_mapped_plan_var = get_post_meta($product_id, '_arm_woocommerce_membership_plan', true);
                $arm_mapped_plan = (isset($arm_mapped_plan_var) && $arm_mapped_plan_var != 0) ? $arm_mapped_plan_var : 0;

                if ($arm_mapped_plan != 0) {
                     $mapped_product_id_array[]=$product_id;
                }
                   
                $cart_items = $woocommerce->cart->get_cart();

                foreach($cart_items as $item => $values) { 
                    $product_id = $values['product_id'];
                    if ($arm_mapped_plan != 0 && count($mapped_product_id_array) < 1) {
                        $mapped_product_id_array[]=$product_id;
                    } else if($arm_mapped_plan != 0) {
                        $woocommerce->cart->remove_cart_item($item);
                    }
                }    
            }


            /*if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                $mapped_product_id_array = array();
                $arm_mapped_plan_var = get_post_meta($product_id, '_arm_woocommerce_membership_post', true);
                $arm_mapped_plan = (isset($arm_mapped_plan_var) && $arm_mapped_plan_var != 0) ? $arm_mapped_plan_var : 0;

                if ($arm_mapped_plan != 0) {
                     $mapped_product_id_array[]=$product_id;
                }
                   
                $cart_items = $woocommerce->cart->get_cart();

                foreach($cart_items as $item => $values) { 
                    $product_id = $values['product_id'];
                    if ($arm_mapped_plan != 0 && count($mapped_product_id_array) < 1) {
                        $mapped_product_id_array[]=$product_id;
                    } else if($arm_mapped_plan != 0) {
                        $woocommerce->cart->remove_cart_item($item);
                    }
                }
            }*/

            // Do nothing with the data and return
            return $cart_item_data;
        }

        function arm_woocommerce_remove_all_quantity_fields($return, $product) {
            global $arm_pay_per_post_feature;

            $product_id = $product->get_id();         
            $arm_mapped_plan_var = get_post_meta($product_id, '_arm_woocommerce_membership_plan', true);
            $arm_mapped_plan = (isset($arm_mapped_plan_var) && $arm_mapped_plan_var != 0) ? $arm_mapped_plan_var : 0;
            if ($arm_mapped_plan != 0) {
                return( true );
            }


            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                $product_id = $product->get_id();         
                $arm_mapped_plan_var = get_post_meta($product_id, '_arm_woocommerce_membership_post', true);
                $arm_mapped_plan = (isset($arm_mapped_plan_var) && $arm_mapped_plan_var != 0) ? $arm_mapped_plan_var : 0;
                if ($arm_mapped_plan != 0) {
                    return( true );
                }
            }

            return $return;
        }

        function arm_woocommerce_update_order_meta($order_id) {
            global $arm_pay_per_post_feature;

            $order = new WC_Order($order_id);
            $items = $order->get_items();
            $product_id = array();
            $arm_mapped_product_plan = $arm_mapped_product_post = array();
            foreach ($items as $item) {
                $product_id[] = $item['product_id'];
            }

            foreach ($product_id as $pid) {
                $arm_mapped_plan_var = get_post_meta($pid, '_arm_woocommerce_membership_plan', true);
                $arm_mapped_plan = (isset($arm_mapped_plan_var) && $arm_mapped_plan_var != 0) ? $arm_mapped_plan_var : 0;
                if ($arm_mapped_plan != 0) {
                    $arm_mapped_product_plan[] = get_post_meta($pid, '_arm_woocommerce_membership_plan', true);
                }
            }

            $arm_mapped_product_plan_serialize = maybe_serialize($arm_mapped_product_plan);

            update_post_meta($order_id, 'arm_mapped_order_product_plans', $arm_mapped_product_plan_serialize);
            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                foreach ($product_id as $pid) {
                    $arm_mapped_plan_var = get_post_meta($pid, '_arm_woocommerce_membership_post', true);
                    $arm_mapped_plan = (isset($arm_mapped_plan_var) && $arm_mapped_plan_var != 0) ? $arm_mapped_plan_var : 0;
                    if ($arm_mapped_plan != 0) {
		    	$_arm_woocommerce_membership_post = get_post_meta($pid, '_arm_woocommerce_membership_post', true);
			if(!empty($_arm_woocommerce_membership_post))
			{
                        	$arm_mapped_product_post[] = $_arm_woocommerce_membership_post;
			}
                    }
                }

                $arm_mapped_product_plan_serialize = maybe_serialize($arm_mapped_product_post);

                update_post_meta($order_id, 'arm_mapped_order_product_post', $arm_mapped_product_plan_serialize);
            }
        }

        function arm_woocommerce_add_member($order_id) {
            global $arm_subscription_plans, $wpdb, $ARMember, $is_multiple_membership_feature, $arm_pay_per_post_feature, $arm_debug_payment_log_id;

            $order = new WC_Order($order_id);
            $customer_id = $order->get_customer_id();
            if (!empty($customer_id) && sizeof($order->get_items()) > 0) {

                $wc_product_amt_arr = array();
                $item_order_total = 0;
                if($is_multiple_membership_feature->isMultipleMembershipFeature)
                {
                    foreach ($order->get_items() as $item_id => $item_data) {
                        
                        // Get an instance of corresponding the WC_Product object
                        $product = $item_data->get_product();
                        
                        //$product_name = $product->get_name(); // Get the product name
                        $item_product_id = $product->get_id(); // Get the product name

                        //$item_quantity = $item_data->get_quantity(); // Get the item quantity

                        $item_product_total = $item_data->get_total(); // Get the item line total
                        $item_total_tax = $item_data->get_total_tax(); // Tax rate code

                        $item_product_total_final = $item_product_total + $item_total_tax;

                        $item_arm_mapped_plan_var = get_post_meta($item_product_id, '_arm_woocommerce_membership_plan', true);
                        $item_arm_mapped_plan = (isset($item_arm_mapped_plan_var) && $item_arm_mapped_plan_var != 0) ? $item_arm_mapped_plan_var : 0;

                        if ($item_arm_mapped_plan != 0) {
                             $wc_product_amt_arr[$item_arm_mapped_plan] = $item_product_total_final;
                        }
                    }
                }
                else {
                   $item_order_total = $order->get_total();
                }
                
                $arm_mapped_product_plans_serialized = get_post_meta($order_id, 'arm_mapped_order_product_plans', true);
                $arm_mapped_product_plans = maybe_unserialize($arm_mapped_product_plans_serialized);
                $member_user_id = $order->get_customer_id();
                $member_email = $order->get_billing_email();
                if (is_super_admin($member_user_id))
                {
                    return true;
                }
                    
                if (user_can($member_user_id, 'administrator'))
                {
                    return;
                }
                
                if(!empty($arm_mapped_product_plans))
                {
                
                    $arm_mapped_product_plans_arr = array();
                    if(!$is_multiple_membership_feature->isMultipleMembershipFeature) {
                        $arm_mapped_product_plans_arr[] = $arm_mapped_product_plans[0];
                    } else {
                        $arm_mapped_product_plans_arr = $arm_mapped_product_plans;
                    }
                    $user_id = $member_user_id;
                    foreach ($arm_mapped_product_plans_arr as $arm_mapped_product_plan) 
                    {
                        
                        $entry_plan = $arm_mapped_product_plan;
                        $mapped_product_amt_total = isset($wc_product_amt_arr[$entry_plan]) ? $wc_product_amt_arr[$entry_plan] : $item_order_total;

                        $new_plan = new ARM_Plan($entry_plan);
                        $plan_type = isset($new_plan->options['payment_type']) ? $new_plan->options['payment_type'] : '';
                        
                        if($new_plan->is_free())
                        {
                            $plan_type = 'one_time';
                        }
                        

                        $is_order_completed_alreay = get_user_meta($user_id, 'arm_order_completed_'.$entry_plan.'_' . $order_id, true);

                        if (isset($is_order_completed_alreay) && !empty($is_order_completed_alreay) && $is_order_completed_alreay == 'yes')
                        {
                            return;
                        }
                        
                        
                        
                        $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_'.$entry_plan, true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                        
                        if($new_plan->is_recurring())
                        {
                            $userPlanData['arm_payment_mode'] = 'manual_subscription';
                            $items = $order->get_items();
                            $product_id = array();
                            foreach ($items as $item) {
                                $product_id[] = $item['product_id'];
                            }

                            $woo_sel_pro_plan_cycle = 0;
                            $arm_get_plan_selected_cycle = get_post_meta($order_id, 'arm_woo_payment_selected_cycle', true);
                            if($arm_get_plan_selected_cycle != ""){
                                //If payment done with woocommerce payment gateway then get selected payment cycle from order meta.
                                $woo_sel_pro_plan_cycle = $arm_get_plan_selected_cycle;
                            }

                            if($woo_sel_pro_plan_cycle == ""){
                                foreach ($product_id as $pid) {
                                    $woo_sel_pro_plan_cycle_tmp = get_post_meta($pid, '_arm_woocommerce_membership_plan_subscription_id', true);
                                    if($woo_sel_pro_plan_cycle_tmp != "") {
                                        $woo_sel_pro_plan_cycle = $woo_sel_pro_plan_cycle_tmp;
                                        break;
                                    }
                                }
                            }

                            $userPlanData['arm_payment_cycle'] = $woo_sel_pro_plan_cycle;
                        }
                        
                            $is_update_plan = true;
                            $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, current_time('mysql')));
                            
                            if(!$is_multiple_membership_feature->isMultipleMembershipFeature){
                                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                                $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                                $old_plan_data = get_user_meta($user_id, 'arm_user_plan_'.$old_plan_id, true); 
                                $old_plan_data = !empty($old_plan_data) ? $old_plan_data : array();
                                $old_plan_data = shortcode_atts($defaultPlanData, $old_plan_data);
                                $oldPlanDetail = $old_plan_data['arm_current_plan_detail'];
                                if (!empty($oldPlanDetail)) {
                                    $old_plan = new ARM_Plan(0);
                                    $old_plan->init((object) $oldPlanDetail);
                                } else {
                                    $old_plan = new ARM_Plan($old_plan_id);
                                }

                                if ($old_plan->exists()) {  
                                    if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                                            $is_update_plan = true;
                                    } 
                                    else {
                                        $change_act = 'immediate';
                                        if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                            if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                                $change_act = $old_plan->downgrade_action;
                                            }
                                            if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                                $change_act = $old_plan->upgrade_action;
                                            }
                                        }
                                        $subscr_effective = $old_plan_data['arm_expire_plan'];
                                        if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                            $is_update_plan = false;
                                            $old_plan_data['arm_subscr_effective'] = $subscr_effective;
                                            $old_plan_data['arm_change_plan_to'] = $entry_plan;
                                            update_user_meta($user_id, 'arm_user_plan_'.$old_plan_id, $old_plan_data);
                                        }
                                    }
                                }
                            }
                            
                            $userPlanData['arm_user_gateway'] = 'woocommerce';


                            update_user_meta($user_id,'arm_user_plan_'.$entry_plan, $userPlanData);
                            if ($is_update_plan && (!$new_plan->isPaidPost)) {
                                
                                $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', true, $arm_last_payment_status);
                            } else {
                                if(!$is_multiple_membership_feature->isMultipleMembershipFeature && (!$new_plan->isPaidPost)){
                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                                }
                                else{
                                    $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'new_subscription');
                                }
                            }

                       
                        update_user_meta($user_id, 'arm_order_completed_'.$entry_plan.'_' . $order_id, 'yes');
                        
                        $entry_id = '0';
                        $entry_tbl = $ARMember->tbl_arm_entries;
                        $user_email = $member_email;
                        $entry_id = $wpdb->get_row( $wpdb->prepare( 'SELECT arm_entry_id FROM '.$entry_tbl.' WHERE arm_entry_email = %s and arm_name = %s and arm_form_id = %d order by arm_entry_id desc', $user_email, 'woocommerce', $order_id ), ARRAY_A );
                        update_user_meta($user_id, 'arm_entry_id', $entry_id['arm_entry_id']);
                        
                        $arm_debug_log_data = array(
                            'userid' => $user_id,
                            'entryplan' => $entry_plan,
                            'orderid' => $order_id,
                            'plantype' => $plan_type,
                        );
                        do_action('arm_payment_log_entry', 'woocommerce', 'order'.$order_id.' payment log entry data', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);

                        $this->arm_store_woocommerce_log($user_id, $entry_plan, $order_id, $plan_type, $mapped_product_amt_total);
                    }
                }
            }

            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                $this->arm_woocommerce_add_paid_post($order_id);
            }

            //Update user meta when order is placed from woocommerce front
            do_action('arm_update_woocommerce_user_meta_external', $order, $customer_id);
        }



        function arm_woocommerce_add_paid_post($order_id) {
            global $arm_subscription_plans, $wpdb, $ARMember, $is_multiple_membership_feature, $is_woocommerce_feature, $arm_pay_per_post_feature, $arm_debug_payment_log_id;

            $order = new WC_Order($order_id);
            $customer_id = $order->get_customer_id();
            if (!empty($customer_id) && sizeof($order->get_items()) > 0) 
            {
                $wc_product_amt_arr = array();
                $item_order_total = 0;
                if($arm_pay_per_post_feature->isPayPerPostFeature)
                {
                    foreach ($order->get_items() as $item_id => $item_data) {
                        
                        // Get an instance of corresponding the WC_Product object
                        $product = $item_data->get_product();
                        
                        //$product_name = $product->get_name(); // Get the product name
                        $item_product_id = $product->get_id(); // Get the product name

                        //$item_quantity = $item_data->get_quantity(); // Get the item quantity

                        $item_product_total = $item_data->get_total(); // Get the item line total
                        $item_total_tax = $item_data->get_total_tax(); // Tax rate code

                        $item_product_total_final = $item_product_total + $item_total_tax;

                        $item_arm_mapped_plan_var = get_post_meta($item_product_id, '_arm_woocommerce_membership_post', true);

                        $item_arm_mapped_plan = (isset($item_arm_mapped_plan_var) && $item_arm_mapped_plan_var != 0) ? $item_arm_mapped_plan_var : 0;

                        if ($item_arm_mapped_plan != 0) {
                             $wc_product_amt_arr[$item_arm_mapped_plan] = $item_product_total_final;
                        }
                    }
                }
                else {
                   $item_order_total = $order->get_total();
                }
                
                $arm_mapped_product_plans_serialized = get_post_meta($order_id, 'arm_mapped_order_product_post', true);
                $arm_mapped_product_plans = maybe_unserialize($arm_mapped_product_plans_serialized);
                $arm_mapped_product_plans = array_filter($arm_mapped_product_plans);
                $member_user_id = $order->get_customer_id();
                $member_email = $order->get_billing_email();

                if (is_super_admin($member_user_id))
                {
                    return true;
                }
                    
                if (user_can($member_user_id, 'administrator'))
                {
                    return;
                }
                
                if(empty($arm_mapped_product_plans))
                {
                    return;
                }

                $arm_mapped_product_plans_arr = array();
                $arm_mapped_product_plans_arr = $arm_mapped_product_plans;
                $user_id = $member_user_id;

                foreach ($arm_mapped_product_plans_arr as $arm_mapped_product_plan) 
                {
                    $entry_plan = $arm_mapped_product_plan;
                    $mapped_product_amt_total = isset($wc_product_amt_arr[$entry_plan]) ? $wc_product_amt_arr[$entry_plan] : $item_order_total;

                    $new_plan = new ARM_Plan($entry_plan);
                    $plan_type = isset($new_plan->options['payment_type']) ? $new_plan->options['payment_type'] : '';
                    
                    if($new_plan->is_free())
                    {
                        $plan_type = 'one_time';
                    }
                    
                    $is_order_completed_alreay = get_user_meta($user_id, 'arm_order_completed_'.$entry_plan.'_' . $order_id, true);

                    if (isset($is_order_completed_alreay) && !empty($is_order_completed_alreay) && $is_order_completed_alreay == 'yes')
                    {
                        return;
                    }
                    
                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_'.$entry_plan, true);
                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                    $userPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                    
                    if($new_plan->is_recurring())
                    {
                        $userPlanData['arm_payment_mode'] = 'manual_subscription';
                        $items = $order->get_items();
                        $product_id = array();
                        foreach ($items as $item) {
                            $product_id[] = $item['product_id'];
                        }

                        $woo_sel_pro_plan_cycle = 0;
                        $arm_get_plan_selected_cycle = get_post_meta($order_id, 'arm_woo_payment_post_selected_cycle', true);
                        if($arm_get_plan_selected_cycle != ""){
                            //If payment done with woocommerce payment gateway then get selected payment cycle from order meta for paid post.
                            $woo_sel_pro_plan_cycle = $arm_get_plan_selected_cycle;
                        }

                        if($woo_sel_pro_plan_cycle == "")
                        {
                            foreach ($product_id as $pid) {
                                $woo_sel_pro_plan_cycle_tmp = get_post_meta($pid, '_arm_woocommerce_membership_post_subscription_id', true);
                                if($woo_sel_pro_plan_cycle_tmp != "") {
                                    $woo_sel_pro_plan_cycle = $woo_sel_pro_plan_cycle_tmp;
                                    break;
                                }
                            }
                        }
                        $userPlanData['arm_payment_cycle'] = $woo_sel_pro_plan_cycle;
                    }
                    
                    $is_update_plan = true;
                    $arm_last_payment_status = $wpdb->get_var($wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1", $user_id, $entry_plan, current_time('mysql')));

                    $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                    $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                    $old_plan_data = get_user_meta($user_id, 'arm_user_plan_'.$old_plan_id, true); 
                    $old_plan_data = !empty($old_plan_data) ? $old_plan_data : array();
                    $old_plan_data = shortcode_atts($defaultPlanData, $old_plan_data);
                    $oldPlanDetail = $old_plan_data['arm_current_plan_detail'];
                    if (!empty($oldPlanDetail)) {
                        $old_plan = new ARM_Plan(0);
                        $old_plan->init((object) $oldPlanDetail);
                    } else {
                        $old_plan = new ARM_Plan($old_plan_id);
                    }

                    if ($old_plan->exists()) {  
                        if ($old_plan->is_lifetime() || $old_plan->is_free() || ($old_plan->is_recurring() && $new_plan->is_recurring())) {
                            $is_update_plan = true;
                        } 
                        else {
                            $change_act = 'immediate';
                            if ($old_plan->enable_upgrade_downgrade_action == 1) {
                                if (!empty($old_plan->downgrade_plans) && in_array($new_plan->ID, $old_plan->downgrade_plans)) {
                                    $change_act = $old_plan->downgrade_action;
                                }
                                if (!empty($old_plan->upgrade_plans) && in_array($new_plan->ID, $old_plan->upgrade_plans)) {
                                    $change_act = $old_plan->upgrade_action;
                                }
                            }
                            $subscr_effective = $old_plan_data['arm_expire_plan'];
                            if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                $is_update_plan = false;
                                $old_plan_data['arm_subscr_effective'] = $subscr_effective;
                                $old_plan_data['arm_change_plan_to'] = $entry_plan;
                                update_user_meta($user_id, 'arm_user_plan_'.$old_plan_id, $old_plan_data);
                            }
                        }
                    }
                    
                    $userPlanData['arm_user_gateway'] = 'woocommerce';

                    update_user_meta($user_id,'arm_user_plan_'.$entry_plan, $userPlanData);
                    if ($is_update_plan) 
                    {    
                        $arm_subscription_plans->arm_update_user_subscription($user_id, $entry_plan, '', false, $arm_last_payment_status);
                    }
                    else 
                    {
                        $arm_subscription_plans->arm_add_membership_history($user_id, $entry_plan, 'change_subscription');
                    }

                   
                    update_user_meta($user_id, 'arm_order_completed_'.$entry_plan.'_' . $order_id, 'yes');
                    
                    $entry_id = '0';
                    $entry_tbl = $ARMember->tbl_arm_entries;
                    $user_email = $member_email;
                    $entry_id = $wpdb->get_row( $wpdb->prepare( 'SELECT arm_entry_id FROM '.$entry_tbl.' WHERE arm_entry_email = %s and arm_name = %s and arm_form_id = %d order by arm_entry_id desc', $user_email, 'woocommerce', $order_id ), ARRAY_A );
                    update_user_meta($user_id, 'arm_entry_id', $entry_id['arm_entry_id']);
                    
                    $arm_debug_log_data = array(
                        'userid' => $user_id,
                        'plan' => $entry_plan,
                        'orderid' => $order_id,
                        'plantype' => $plan_type,
                    );
                    do_action('arm_payment_log_entry', 'woocommerce', 'order'.$order_id.' paid post store log data', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);

                    $is_woocommerce_feature->arm_store_woocommerce_log($user_id, $entry_plan, $order_id, $plan_type, $mapped_product_amt_total);
                }
            }
        }

        function arm_woocommerce_cancel_membership_from_order($order_id) {
            global $ARMember, $arm_subscription_plans, $arm_members_class, $woocommerce, $is_multiple_membership_feature, $arm_pay_per_post_feature, $arm_debug_payment_log_id;

            $order = new WC_Order($order_id);
            $member_user_id = $order->get_customer_id();

            if (user_can($member_user_id, 'administrator') || is_super_admin($member_user_id))
            {
                return true;
            }

            if (!empty($member_user_id) && sizeof($order->get_items()) > 0) 
            {
                $arm_mapped_product_plans_serialized = get_post_meta($order_id, 'arm_mapped_order_product_plans', true);
                $arm_mapped_product_plans = maybe_unserialize($arm_mapped_product_plans_serialized);
                
                $user_id = $member_user_id;
                
                if(!empty($arm_mapped_product_plans))
                {
                    $arm_mapped_product_plans_arr = array();
                    if(empty($is_multiple_membership_feature->isMultipleMembershipFeature)) {
                        $arm_mapped_product_plans_arr[] = $arm_mapped_product_plans[0];
                    } else {
                        $arm_mapped_product_plans_arr = $arm_mapped_product_plans;
                    }
                    
                    foreach ($arm_mapped_product_plans_arr as $arm_mapped_product_plan) 
                    {
                        $plan_id = $arm_mapped_product_plan;
                        
                        $is_order_completed_alreay = get_user_meta($user_id, 'arm_order_completed_'.$plan_id.'_' . $order_id, true);
                        if (!isset($is_order_completed_alreay) || empty($is_order_completed_alreay) || $is_order_completed_alreay != 'yes')
                        {
                            continue;
                        }
                        
                        $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                        $user_plans = !empty($user_plans) ? $user_plans : array();
                        if(!in_array($plan_id, $user_plans)){
                            continue;
                        }

                        $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $PlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                        
                        $arm_old_plan_detail = $PlanData['arm_current_plan_detail'];
                        $arm_user_old_payment_cycle = '';
                        if (!empty($arm_old_plan_detail)) {
                            $plan = new ARM_Plan(0);
                            $plan->init((object) $arm_old_plan_detail);
                        } else {
                            $plan = new ARM_Plan($plan_id);
                        }
                        
                        $plan = new ARM_Plan($plan_id);

                        if ($plan->exists()) 
                        {    
                            $cancel_plan_action = isset($plan->options['cancel_plan_action']) ? $plan->options['cancel_plan_action'] : 'immediate';
                            $PlanData['arm_cencelled_plan'] = 'yes';
                            update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $PlanData);
                            if (($plan->is_paid() && !$plan->is_lifetime() && $plan->is_recurring()) || ( $plan->is_paid() || $plan->is_lifetime() || $plan->is_free() )) {
                                 
                                get_user_meta($user_id, 'arm_order_completed_'.$plan_id.'_' . $order_id, '');
                                if ($cancel_plan_action == 'immediate') 
                                {
                                    //Update Last Subscriptions Log Detail
                                    do_action('arm_before_update_user_subscription', $user_id, '0');
                                    $arm_subscription_plans->arm_add_membership_history($user_id, $plan_id, 'cancel_subscription');
                                    do_action('arm_cancel_subscription', $user_id, $plan_id);
                                    $arm_subscription_plans->arm_clear_user_plan_detail($user_id, $plan_id);
                                    $cancel_plan_act = isset($plan->options['cancel_action']) ? $plan->options['cancel_action'] : 'block';
                                    if ($arm_subscription_plans->isPlanExist($cancel_plan_act)) {
                                         $arm_members_class->arm_new_plan_assigned_by_system($cancel_plan_act, $plan_id, $user_id); 
                                    }
                                }
                            }
                        }
                    }
                }

                if($arm_pay_per_post_feature->isPayPerPostFeature)
                {
                    $arm_mapped_product_posts_serialized = get_post_meta($order_id, 'arm_mapped_order_product_post', true);
                    $arm_mapped_product_posts = maybe_unserialize($arm_mapped_product_posts_serialized);
                    
                    if(!empty($arm_mapped_product_posts)) 
                    {
                        $arm_mapped_product_plans_arr = array();
                        $arm_mapped_product_plans_arr[] = $arm_mapped_product_posts[0];
                        
                        foreach ($arm_mapped_product_plans_arr as $arm_mapped_product_plan) 
                        {
                            $plan_id = $arm_mapped_product_plan;
                            
                            $is_order_completed_alreay = get_user_meta($user_id, 'arm_order_completed_'.$plan_id.'_' . $order_id, true);
                            if (!isset($is_order_completed_alreay) || empty($is_order_completed_alreay) || $is_order_completed_alreay != 'yes')
                            {
                                continue;
                            }
                            
                            $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                            $user_plans = !empty($user_plans) ? $user_plans : array();
                            if(!in_array($plan_id, $user_plans)){
                                continue;
                            }

                            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                            $PlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                            
                            $arm_old_plan_detail = $PlanData['arm_current_plan_detail'];
                            $arm_user_old_payment_cycle = '';
                            if (!empty($arm_old_plan_detail)) {
                                $plan = new ARM_Plan(0);
                                $plan->init((object) $arm_old_plan_detail);
                            } else {
                                $plan = new ARM_Plan($plan_id);
                            }
                            
                            $plan = new ARM_Plan($plan_id);

                            if ($plan->exists()) 
                            {    
                                $cancel_plan_action = isset($plan->options['cancel_plan_action']) ? $plan->options['cancel_plan_action'] : 'immediate';
                                $PlanData['arm_cencelled_plan'] = 'yes';
                                update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $PlanData);
                                if (($plan->is_paid() && !$plan->is_lifetime() && $plan->is_recurring()) || ( $plan->is_paid() || $plan->is_lifetime() || $plan->is_free() )) 
                                {     
                                    get_user_meta($user_id, 'arm_order_completed_'.$plan_id.'_' . $order_id, '');
                                    if ($cancel_plan_action == 'immediate') 
                                    {
                                        //Update Last Subscriptions Log Detail
                                        do_action('arm_before_update_user_subscription', $user_id, '0');
                                        $arm_subscription_plans->arm_add_membership_history($user_id, $plan_id, 'cancel_subscription');
                                        do_action('arm_cancel_subscription', $user_id, $plan_id);
                                        $arm_subscription_plans->arm_clear_user_plan_detail($user_id, $plan_id);
                                        $cancel_plan_act = isset($plan->options['cancel_action']) ? $plan->options['cancel_action'] : 'block';
                                        if ($arm_subscription_plans->isPlanExist($cancel_plan_act)) 
                                        {
                                             $arm_members_class->arm_new_plan_assigned_by_system($cancel_plan_act, $plan_id, $user_id); 
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
		
		$arm_debug_log_data = array(
                    'user_id' => $member_user_id,
                    'order_id' => $order_id,
                );
                do_action('arm_payment_log_entry', 'woocommerce', 'order'.$order_id.' cancel membership order data', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);
            }
        }

        function arm_store_woocommerce_log($user_id = 0, $plan_id = 0, $order_id = 0, $plan_type = '', $product_or_order_amt='0') {
            global $wpdb, $ARMember, $arm_payment_gateways;

            $order = new WC_Order($order_id);
            $user_info = get_userdata($user_id);
            $user_email = $user_info->user_email;

            $arm_entry_id = get_user_meta($user_id, 'arm_wooc_gateway_entry_id', true);
            $arm_tax_percentage = get_user_meta($user_id, 'arm_wooc_gateway_tax_'.$arm_entry_id, true);
            $arm_coupon_code = get_user_meta($user_id, 'arm_wooc_gateway_coupon_'.$arm_entry_id, true);

            $arm_extra_vars = array();

            if(!empty($arm_tax_percentage)){
                $arm_plan = new ARM_Plan($plan_id);
                $arm_plan_amount = $arm_plan->amount;

                $arm_tax_amount = ($arm_plan_amount * $arm_tax_percentage) / 100;

                $arm_extra_vars = array(
                    'tax_amount' => number_format($arm_tax_amount, 2),
                    'tax_percentage' => number_format($arm_tax_percentage, 2),
                );
            }
            
            $payment_data = array(
                'arm_user_id' => $user_id,
                'arm_first_name' => $user_info->first_name,
                'arm_last_name' => $user_info->last_name,
                'arm_plan_id' => (!empty($plan_id) ? $plan_id : 0),
                'arm_payment_gateway' => 'woocommerce',
                'arm_payment_type' => $plan_type,
                'arm_token' => '',
                'arm_payer_email' => $user_email,
                'arm_receiver_email' => '',
                'arm_transaction_id' => $order_id,
                'arm_transaction_payment_type' => $plan_type,
                'arm_transaction_status' => 'success',
                'arm_payment_date' => $order->order_date,
                'arm_amount' => $product_or_order_amt,
                'arm_currency' => $order->get_currency(),
                'arm_coupon_code' => $arm_coupon_code,
                'arm_extra_vars' => maybe_serialize($arm_extra_vars),
                'arm_created_date' => current_time('mysql')
            );

            if(!empty($arm_coupon_code)){
                //Get coupon details
                global $arm_manage_coupons;
                $arm_coupon_details = $arm_manage_coupons->arm_get_coupon($arm_coupon_code);
                if(!empty($arm_coupon_details))
                {
                    $arm_coupon_discount = $arm_coupon_details['arm_coupon_discount'];
                    $arm_coupon_discount_type = $arm_coupon_details['arm_coupon_discount_type'];
                    $arm_coupon_on_each_subs = $arm_coupon_details['arm_coupon_on_each_subscriptions'];

                    $payment_data['arm_coupon_discount'] = $arm_coupon_discount;
                    $payment_data['arm_coupon_discount_type'] = $arm_coupon_discount_type;
                    $payment_data['arm_coupon_on_each_subscriptions'] = $arm_coupon_on_each_subs;
                }
            }

            $arm_payment_gateways->arm_save_payment_log($payment_data);
        }
        
        function arm_woocommerce_add_payment_gateway($default_payment_gateway)
        {
            global $arm_payment_gateways;
            $default_payment_gateway['woocommerce'] = array('gateway_name' => $arm_payment_gateways->arm_gateway_name_by_key('woocommerce'));
               
            return $default_payment_gateway;
        }
        
        function arm_woocommerce_add_payment_gateway_name($gatewayNames)
        {
            $gatewayNames['woocommerce'] =  __('WooCommerce', 'ARMember');
            return $gatewayNames;
        }
        
        function arm_woocommerce_make_order_status_complete_for_virtual_products($order_status, $order_id)
        {
            global $arm_pay_per_post_feature;
            $order = new WC_Order( $order_id );
                $order_get_status = $order->get_status();
                if ( 'processing' == $order_status && ('on-hold' == $order_get_status || 'pending' == $order_get_status || 'processing' == $order_get_status ) ) {

                    $virtual_order = null;

                    global $woocommerce;

                    $order_get_items = $order->get_items();
                    if ( count( $order_get_items ) > 0 ) {

                        foreach( $order_get_items as $item ) {

                            if ( 'line_item' == $item['type'] ) {                                
                                if( version_compare( $woocommerce->version, '4.4.0', ">=" ) )
                                {
                                     $_product = $item->get_product();
                                }
                                else {
                                    $_product = $order->get_product_from_item( $item );
                                }


                            if ( ! $_product->is_virtual() ) {
                              // once we've found one non-virtual product we know we're done, break out of the loop
                              $virtual_order = false;
                              break;
                            } else {
                                $pid  = $item['product_id'];
                                $arm_mapped_plan_var = get_post_meta($pid, '_arm_woocommerce_membership_plan', true);
                                $arm_mapped_plan = (isset($arm_mapped_plan_var) && $arm_mapped_plan_var != 0) ? $arm_mapped_plan_var : 0;
                                if ($arm_mapped_plan != 0) {
                                    $virtual_order = true;
                                }


                                if($arm_pay_per_post_feature->isPayPerPostFeature)
                                {
                                    $arm_mapped_plan_var = get_post_meta($pid, '_arm_woocommerce_membership_post', true);
                                    $arm_mapped_plan = (isset($arm_mapped_plan_var) && $arm_mapped_plan_var != 0) ? $arm_mapped_plan_var : 0;
                                    if ($arm_mapped_plan != 0) {
                                        $virtual_order = true;
                                    }
                                }
                            }
                        }
                    }
                }

                // virtual order, mark as completed
                if ( $virtual_order ) {
                    return 'completed';
                }
            }

            // non-virtual order, return original status
            return $order_status;
        }
        
        function arm_woocommerce_add_currency($all_currency)
        {
            $arm_woocommerce_currency_array =  apply_filters( 'woocommerce_currency_symbols', array(
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => '&fnof;',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x10da;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;. &#x645;.',
            'MAD' => '&#x62f;.&#x645;.',
            'MDL' => 'L',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STD' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'Fr',
            'XCD' => '&#36;',
            'XOF' => 'Fr',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
            ) );

            $all_currency = array_merge($all_currency, $arm_woocommerce_currency_array); 

            return $all_currency; 
        }

    }

}
global $is_woocommerce_feature;
$is_woocommerce_feature = new ARM_wocommerce_feature();