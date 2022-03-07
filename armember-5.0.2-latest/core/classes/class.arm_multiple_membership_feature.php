<?php
if (!class_exists('ARM_multiple_membership_feature')) {

    class ARM_multiple_membership_feature {

        var $isMultipleMembershipFeature;

        function __construct() {
           
            $is_multiple_membership_feature = get_option('arm_is_multiple_membership_feature');
            $this->isMultipleMembershipFeature = ($is_multiple_membership_feature == '1') ? true : false;
            //add_action('arm_add_new_custom_add_on', array($this, 'arm_add_multiple_membership_addon'), 10);
            add_action('arm_deactivate_feature_settings', array($this, 'arm_multiple_membership_update_feature_settings'), 10, 1);
           
         
        }
        
       
        function arm_multiple_membership_update_feature_settings($posted_data)
        {
            global $wp, $wpdb, $wp_rewrite, $ARMember;
            $features_options = $posted_data['arm_features_options'];
            $arm_features_status = (!empty($posted_data['arm_features_status'])) ? $posted_data['arm_features_status'] : 0;
            if ($features_options == 'arm_is_multiple_membership_feature') {
                
                $args = array(
                    'meta_query' => array(
                        array(
                            'key' => 'arm_user_plan_ids',
                            'value' => '',
                            'compare' => '!='
                        ),
                    )
                );

                $amTotalUsers = get_users($args);
                $morethanoneplan = 0;
                if (!empty($amTotalUsers)) {
                    foreach ($amTotalUsers as $usr) {
                        $user_id = $usr->ID;
                        $arm_user_plan = get_user_meta($user_id,'arm_user_plan_ids', true);
                        $arm_user_paid_post = get_user_meta($user_id,'arm_user_post_ids', true);
                        $arm_user_gift_plan = get_user_meta($user_id, 'arm_user_gift_ids', true);
                        
                        if(!empty($arm_user_plan) && is_array($arm_user_plan)){
                            $count = 0;
                            foreach($arm_user_plan as $plan_id)
                            {
                                if(!empty($arm_user_paid_post) && array_key_exists($plan_id, $arm_user_paid_post))
                                {
                                    continue;
                                }
                                if(!empty($arm_user_gift_plan) && in_array($plan_id, $arm_user_gift_plan))
                                {
                                    continue;
                                }

                                if(!empty($plan_id)){
                                    $count++;
                                }
                                if($count > 1){
                                    $morethanoneplan = 1;
                                    break;
                                }
                            }
                        }
                    }
                }
              
                if($morethanoneplan == 1){
                    $response = array('type' => 'wocommerce_error', 'msg' => __("One or more users have multiple membership, so addon can't be deactivated.", 'ARMember'));
                            echo json_encode($response);
                            die();
                }
            }
        }       
        
        
    }

}
global $is_multiple_membership_feature;
$is_multiple_membership_feature = new ARM_multiple_membership_feature();
