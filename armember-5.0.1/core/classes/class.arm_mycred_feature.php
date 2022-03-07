<?php
if (!class_exists('ARM_mycred_feature') && class_exists('myCRED_Hook'))
{
    class ARM_mycred_feature extends myCRED_Hook
    {
        var $ismyCREDFeature;
        public function __construct($hook_prefs=array(), $type='mycred_default')
        {
            global $wpdb, $ARMember, $arm_subscription_plans;
            $arm_is_mycred_feature = get_option('arm_is_mycred_feature');
            $this->ismyCREDFeature = ($arm_is_mycred_feature == '1') ? true : false;
            
            if($this->ismyCREDFeature)
            {
                $armemeber_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
                add_action('admin_head', array($this, 'arm_mycred_script_style'));
                
                
                if(!empty($armemeber_plans))
                {
                    foreach($armemeber_plans as $armemeber_plans_keys => $armemeber_plans_val)
                    {
                        $arm_mycred_points_defaults['arm_mycred_point_key_' . $armemeber_plans_val['arm_subscription_plan_id']] = array(
                                    'creds' => 0,
                                    'log'   => '%plural% '.__('for ARMember Plan', 'ARMember').' ' . $armemeber_plans_val['arm_subscription_plan_name'],
                                    'arm_mycred_reward' => 0,               
                        );
                    }
                    if (!empty($arm_mycred_points_defaults))
                    {
                        parent::__construct( array(
                                    'id'       => 'arm_mycred',
                                    'defaults' => $arm_mycred_points_defaults,
                        ), $hook_prefs, $type );
                    }
                }
            }
        }


        function arm_mycred_script_style()
        {
            global $pagenow;
            if (current_user_can('administrator'))
            {
                wp_enqueue_style('arm_mycred_hook_admin_css', MEMBERSHIP_URL . '/css/arm_mycred_hook_admin.css', array(), MEMBERSHIP_VERSION);
            }
        }

        function run() {
            add_action( 'arm_after_add_new_user', array( $this, 'arm_mycred_add_point_to_user' ), 10, 2 );
            add_action( 'arm_after_add_transaction', array( $this, 'arm_after_add_mycred_add_point_transaction' ), 10, 1 );
            add_action( 'arm_after_recurring_payment_success_outside', array($this,'arm_mycred_add_point_recurring_transaction'), 10, 5);
            add_action( 'arm_after_user_plan_renew', array($this,'arm_mycred_add_point_renew_transaction'), 10, 2);

        }

        function arm_mycred_add_point_to_user($arm_user_id, $posted_data){
            global $wpdb,$ARMember;

            
	    $arm_plan_id = isset( $posted_data['subscription_plan'] ) ? $posted_data['subscription_plan'] : 0;
            if ( $arm_plan_id == 0 ) {
                $arm_plan_id = isset($posted_data['_subscription_plan']) ? $posted_data['_subscription_plan'] : 0;
            }

            $arm_mycred_pgateway = isset($posted_data['payment_gateway']) ? $posted_data['payment_gateway'] : '';
            if ($arm_mycred_pgateway == '') {
                $arm_mycred_pgateway = isset($posted_data['_payment_gateway']) ? $posted_data['_payment_gateway'] : '';
            }
            
            if ($arm_mycred_pgateway != '' && $arm_plan_id > 0) 
            {
                $arm_plan_txn_id = isset($arm_log_data['arm_log_id']) ? $arm_log_data['arm_log_id'] : '';

                $is_success_payment = 0;
                if ($arm_mycred_pgateway == 'bank_transfer') {
                    $arm_mycred_entry = $wpdb->get_row($wpdb->prepare("SELECT `arm_transaction_status` FROM `{$ARMember->tbl_arm_payment_log}` WHERE `arm_log_id` = %d ", $arm_plan_txn_id), OBJECT);
                    if( isset($arm_mycred_entry->arm_transaction_status) && $arm_mycred_entry->arm_transaction_status == 1 ){
                        $is_success_payment = 1;
                    }
                } else {
                    $arm_mycred_entry = $wpdb->get_row($wpdb->prepare("SELECT `arm_transaction_status` FROM `{$ARMember->tbl_arm_payment_log}` WHERE `arm_user_id` = %d AND `arm_plan_id` = %d AND `arm_log_id`= %d ORDER BY `arm_log_id` DESC LIMIT 1", $arm_user_id, $arm_plan_id, $arm_plan_txn_id), OBJECT);
                    if( isset($arm_mycred_entry->arm_transaction_status) && $arm_mycred_entry->arm_transaction_status=='success'){
                        $is_success_payment = 1;
                    }

                }
		
		$arm_debug_log_data = "UserID=".$arm_user_id.", PlanID=".$arm_plan_id.", gateway=".$arm_mycred_pgateway.", txn_id -> ".$arm_plan_txn_id;
                do_action('arm_payment_log_entry', 'mycred', 'Add point to user data', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);
            }
            
            
            if( !empty($is_success_payment) && $is_success_payment != 1)
            {
                if ($arm_user_id && $arm_plan_id>0)
                {
                    
                    $key = 'arm_mycred_point_key_' . $arm_plan_id;
                    
                    
                    if ($this->prefs[$key]['creds']>0){
                        $this->core->add_creds(
                            $key,
                            $arm_user_id,
                            $this->prefs[$key]['creds'],
                            $this->prefs[$key]['log'],
                            $arm_plan_id
                        );                          
                    }
                }
            }
            else
            {
                if ($arm_user_id && $arm_plan_id>0)
                {
                    
                    
                    $key = 'arm_mycred_point_key_' . $arm_plan_id;
                    
                    
                    if ($this->prefs[$key]['creds']>0){
                        $this->core->add_creds(
                            $key,
                            $arm_user_id,
                            $this->prefs[$key]['creds'],
                            $this->prefs[$key]['log'],
                            $arm_plan_id
                        );                          
                    }
                }
            }
        }

        function arm_after_add_mycred_add_point_transaction($arm_log_data){
            global $ARMember, $arm_debug_payment_log_id;
            if( isset($arm_log_data['arm_payment_gateway']) ) 
            {
                global $wpdb, $ARMember, $arm_payment_gateways;
                $arm_user_id = isset($arm_log_data['arm_user_id']) ? $arm_log_data['arm_user_id'] : 0;
                $arm_plan_id = isset( $arm_log_data['arm_plan_id'] ) ? $arm_log_data['arm_plan_id'] : 0;
                if($arm_user_id == 0){ return; }

                $entry_id = get_user_meta($arm_user_id, 'arm_entry_id');

                if(empty($entry_id)) { return; }

                $arm_tbl_entry = $ARMember->tbl_arm_entries;
                $entry_data_value = $wpdb->get_row($wpdb->prepare("SELECT `arm_entry_value` FROM `{$arm_tbl_entry}` WHERE `arm_user_id` = %d AND `arm_entry_id` = %d ", $arm_user_id, $entry_id[0]), ARRAY_A);
                $entry_data = maybe_unserialize($entry_data_value['arm_entry_value']);


                $arm_mycred_pgateway = isset($arm_log_data['arm_payment_gateway']) ? $arm_log_data['arm_payment_gateway'] : '';
                    if ($arm_mycred_pgateway == '') {
                        $arm_mycred_pgateway = isset($entry_data['payment_gateway']) ? $entry_data['payment_gateway'] : '';
                    }


                $is_success_payment = 0;
                if ($arm_mycred_pgateway != '' && $arm_plan_id > 0) 
                {
                    $arm_plan_txn_id = isset($arm_log_data['arm_log_id']) ? $arm_log_data['arm_log_id'] : '';

                    if ($arm_mycred_pgateway == 'bank_transfer') 
                    {
                        //$arm_plan_txn_id = isset($arm_log_data['bank_transfer']['transaction_id']) ? $arm_log_data['bank_transfer']['transaction_id'] : '';
                        $arm_mycred_entry = $wpdb->get_row($wpdb->prepare("SELECT `arm_transaction_status`, `arm_amount` FROM `{$ARMember->tbl_arm_payment_log}` WHERE `arm_log_id` = %d ", $arm_plan_txn_id), OBJECT);
                        if( isset($arm_mycred_entry->arm_transaction_status) && $arm_mycred_entry->arm_transaction_status == 1 ){
                            $is_success_payment = 1;
                        }
                    } else {
                        $arm_mycred_entry = $wpdb->get_row($wpdb->prepare("SELECT `arm_transaction_status`, `arm_amount` FROM `{$ARMember->tbl_arm_payment_log}` WHERE `arm_user_id` = %d AND `arm_plan_id` = %d AND arm_log_id= %d ORDER BY `arm_log_id` DESC LIMIT 1", $arm_user_id, $arm_plan_id, $arm_plan_txn_id), OBJECT);
                        if( isset($arm_mycred_entry->arm_transaction_status) && $arm_mycred_entry->arm_transaction_status == 'success' ){
                            $is_success_payment = 1;
                        }

                    }
                }


                if( !empty($is_success_payment) && $is_success_payment != 1)
                {
                    if ($arm_user_id && $arm_plan_id>0)
                    {
                        
                        $key = 'arm_mycred_point_key_' . $arm_plan_id;
                        
                    
                        if ($this->prefs[$key]['creds']>0)
                        {

                            $result_data = $this->core->add_creds(
                                $key,
                                $arm_user_id,
                                $this->prefs[$key]['creds'],
                                $this->prefs[$key]['log'],
                                $arm_plan_id
                            );
			    do_action('arm_payment_log_entry', 'mycred', 'mycred add point transaction', 'armember', $this->prefs[$key]['creds'], $arm_debug_payment_log_id);

                        }
                    }   
                }
                else
                {
                    if ($arm_user_id && $arm_plan_id>0)
                    {
                        if($arm_mycred_pgateway == "mycred"){
                            $mycred_current_balance = $this->arm_get_mycred_points_by_user($arm_user_id);

                            $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                            $mycred_options = $all_payment_gateways['mycred'];
                            $exchange_point = !empty($mycred_options['point_exchange']) ? $mycred_options['point_exchange'] : 0;
                            $discount_amt = !empty($arm_mycred_entry->arm_amount) ? $arm_mycred_entry->arm_amount : 0;
                            $mycred_exchange_rate = $this->arm_convert_amount_to_points($discount_amt, $exchange_point);

                            if($mycred_current_balance > 0 && $mycred_current_balance > $mycred_exchange_rate) {
                                $point_status = $this->arm_update_mycred_points_by_user($arm_user_id, $mycred_exchange_rate, $arm_plan_id);
                            }
                        }

                        $key = 'arm_mycred_point_key_' . $arm_plan_id;
                        
                        if($is_success_payment == 1){
                            if ($this->prefs[$key]['creds']>0){
                                $this->core->add_creds(
                                    $key,
                                    $arm_user_id,
                                    $this->prefs[$key]['creds'],
                                    $this->prefs[$key]['log'],
                                    $arm_plan_id
                                );
                                do_action('arm_payment_log_entry', 'mycred', 'mycred add point transaction', 'armember', $this->prefs[$key]['creds'], $arm_debug_payment_log_id);
                            }
                        }
                   }
                }
                
            }
            
        }

        function arm_mycred_add_point_recurring_transaction($arm_user_id, $arm_plan_id, $payment_gateway = '', $payment_mode = '', $user_subsdata = ''){
            global $ARMember, $arm_debug_payment_log_id;

            if ($arm_user_id && $arm_plan_id>0){
                
                $key = 'arm_mycred_point_key_' . $arm_plan_id;
                
                if(!isset($this->prefs[$key]['arm_mycred_reward']))
                {
                    return;
                }
                
                if (isset($this->prefs[$key]['creds']) && $this->prefs[$key]['creds']>0){
                    $this->core->add_creds(
                        $key,
                        $arm_user_id,
                        $this->prefs[$key]['creds'],
                        $this->prefs[$key]['log'],
                        $arm_plan_id
                    );
		    $arm_debug_log_data = "UserID=".$arm_user_id.", Plan ID=".$arm_plan_id.", gateway=".$payment_gateway.", Payment Mode=".$payment_mode.", points added=".$this->prefs[$key]['creds'];
                    do_action('arm_payment_log_entry', 'mycred', 'mycred add point recurring transaction details', 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);
                }
            }   
        }
        function arm_mycred_add_point_renew_transaction($arm_user_id, $arm_plan_id)
        {
            global $ARMember;

            
            if ($arm_user_id && $arm_plan_id>0){
                
                $key = 'arm_mycred_point_key_' . $arm_plan_id;
                
                if(!isset($this->prefs[$key]['arm_mycred_reward']))
                {
                    return;
                }
                
                if (isset($this->prefs[$key]['creds']) && $this->prefs[$key]['creds']>0){
                    $this->core->add_creds(
                        $key,
                        $arm_user_id,
                        $this->prefs[$key]['creds'],
                        $this->prefs[$key]['log'],
                        $arm_plan_id
                    );                          
                }
            }
        }
        function preferences()
        {
            
            global $wpdb, $ARMember, $arm_subscription_plans;
            $armemeber_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
            if(!empty($armemeber_plans))
            {
            ?>
            <div class="arm_mycred_points_forms" data-ttl-div="">
                <div class="arm_mycred_forms_label">
                    <div class="arm_mycred_plan_label">
                        <label><?php _e('Membership Plan(s)', 'ARMember');?></label>
                    </div>
                    <div class="arm_mycred_point_label">
                    <label><?php _e('Mycred Point', 'ARMember');?></label>
                    </div>
                    <div class="arm_mycred_reward_label">
                        <label><?php _e('Occurance', 'ARMember');?></label>
                    </div>
                    <div class="arm_mycred_log_label">
                        <label><?php _e('Log Template', 'ARMember');?></label>
                    </div>
                </div>
            <?php
                foreach($armemeber_plans as $armemeber_plans_keys => $armemeber_plans_val)
                {
                 $key = "arm_mycred_point_key_".$armemeber_plans_val['arm_subscription_plan_id'];
            ?>
                <div class="arm_mycred_point_form_fields">
                <div class="arm_mycred_member_plans arm_mycred_point_form_field">
                    <label><?php echo $armemeber_plans_val['arm_subscription_plan_name']; ?></label>
                </div>
                <div class="arm_mycred_point arm_mycred_point_form_field">
                    <input type="number" name="<?php echo $this->field_name(array($key=>'creds'));?>" id="<?php echo $this->field_id(array($key=>'creds')); ?>" value="<?php echo esc_attr($this->core->number($this->prefs[$key]['creds']));?>">
                </div>
                <div class="arm_mycred_reward arm_mycred_point_form_field">
                    <select class="arm_plans_mycred" id="<?php echo $this->field_id(array($key=>'arm_mycred_reward')); ?>" name="<?php echo $this->field_name(array($key=>'arm_mycred_reward'));?>">
                        <option value=""><?php _e('Select Reward', 'ARMember');?></option>
                        <?php $arm_mycred_reward_selected = ($this->prefs[$key]['arm_mycred_reward']==0) ? 'selected' : '';?>
                        <option value="0" <?php echo $arm_mycred_reward_selected;?>><?php _e('Just Once', 'ARMember');?></option>
                        <?php $arm_mycred_reward_selected = ($this->prefs[$key]['arm_mycred_reward']==1) ? 'selected' : '';?>
                        <option value="1" <?php echo $arm_mycred_reward_selected;?>><?php _e('Everytime', 'ARMember');?></option>
                    </select>
                </div>
                <div class="arm_mycred_log_template arm_mycred_point_form_field">
                    <input type="text" name="<?php echo $this->field_name(array($key=>'log'));?>" id="<?php echo $this->field_id('log'); ?>" value="<?php echo esc_attr($this->prefs[$key]['log']);?>">
                </div>
                
                </div>
            <?php 
                }
                ?>
            </div>
            <?php
            }
            ?>
            <div class="armclear"></div>
            <?php
        }

        function arm_get_mycred_points_by_user($user_id) {
            $mycred_current_balance = 0;
            if($user_id > 0 && function_exists('mycred_get_users_balance') ) {
                $mycred_current_balance = mycred_get_users_balance( $user_id );
            }
            return $mycred_current_balance;
        }

        function arm_update_mycred_points_by_user($user_id, $mycred_exchange_rate, $plan_id) {
            $return_val = false;
            if($user_id > 0 && $plan_id > 0) {
                $mycred_current_balance = $this->arm_get_mycred_points_by_user( $user_id );
                
                if ($mycred_current_balance > $mycred_exchange_rate){
                    $mycred_exchange_rate = -1 * ceil(((float)$mycred_exchange_rate));
                    $key = 'arm_mycred_point_key_' . $plan_id;
                    $this->core->add_creds(
                        $key,
                        $user_id,
                        $mycred_exchange_rate,
                        $this->prefs[$key]['log'],
                        $plan_id
                    );
                    $return_val = true;
		    
		    $arm_debug_log_data = "UserID=".$user_id.", exchange rate=".$mycred_exchange_rate.", plan id=".$plan_id;
                    do_action('arm_payment_log_entry', 'mycred', 'Update mycred points for user '.$user_id, 'armember', $arm_debug_log_data, $arm_debug_payment_log_id);
                }
            }
            return $return_val;
        }

        function arm_convert_amount_to_points($amount, $convert_point) {
            $return_points = 0;
            if($amount > 0 && $convert_point > 0) {
                $return_points = $amount / (1 * $convert_point);
            }
            return $return_points;
        }
        
    }
    
}

global $arm_mycred_feature;
$arm_mycred_feature = new ARM_mycred_feature();