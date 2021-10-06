<?php
if (!class_exists('ARMwidgetForm')) {

    class ARMwidgetForm extends WP_Widget {

        function __construct() {
            parent::__construct(
                    'arm_member_form_widget', __('ARMember Forms', 'ARMember'), array('description' => __('Display Member Form', 'ARMember'))
            );
            add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
        }

        public function widget($args, $instance) {
            global $arm_member_forms, $ARMember, $is_globalcss_added,$wpdb;
            echo $args['before_widget'];
            $get_global_css = $ARMember->arm_set_global_css(false);
            echo $get_global_css;
            if (!empty($instance['title'])) {
                echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
            }
            $form_id = isset($instance['form_id']) ? $instance['form_id'] : 0;
            $arm_logged_in_message= isset($instance['logged_in_message']) ? $instance['logged_in_message'] : __('You are already logged in.', 'ARMember');
            if (!empty($form_id) && $form_id != 0) {
                $form_type = $wpdb->get_results("SELECT `arm_form_type` FROM `".$ARMember->tbl_arm_forms."` WHERE `arm_form_id` = {$form_id}");
                $form_type = $form_type[0]->arm_form_type;
                $logged_in_msg = "";
                if( $form_type != 'change_password' ){
                    $logged_in_msg = 'logged_in_message="'.$arm_logged_in_message.'"';
                }
                echo do_shortcode('[arm_form id="' . $form_id . '" widget="true" ' . $logged_in_msg . ']');
            } else {
                _e('There is no any form found.', 'ARMember');
            }
            echo $args['after_widget'];
        }

        public function form($instance) {
            global $wp, $wpdb, $ARMember, $arm_member_forms;
            $title = !empty($instance['title']) ? $instance['title'] : '';
            $form_id = !empty($instance['form_id']) ? $instance['form_id'] : 0;
            $logged_in_msg = isset($instance['logged_in_message']) ? $instance['logged_in_message'] : __('You are already logged in','ARMember');

            $arm_forms = $wpdb->get_results("SELECT `arm_form_id`, `arm_form_label`, `arm_form_type` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type` NOT LIKE 'template' ORDER BY `arm_form_id` DESC", ARRAY_A);
            $form_type = '';
            if ($form_id > 0) {
                $arm_selected_form_type = $wpdb->get_results("SELECT `arm_form_type` FROM `".$ARMember->tbl_arm_forms."` WHERE `arm_form_id` = ".$form_id);
                $form_type = $arm_selected_form_type[0]->arm_form_type;
            }
            ?>
            <script type="text/javascript">
                function arm_update_widget_form_type(object){
                    var $this = jQuery(object);
                    var value = $this.val();
                    if( value == '' ){
                        return false;
                    }
                    var form_type = $this.find('option[value='+value+']').attr('data-type');
                    if( form_type == 'change_password' ){
                        jQuery('p#arm_logged_in_message').hide();
                    } else {
                        jQuery('p#arm_logged_in_message').show();
                    }
                }
            </script>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'ARMember'); ?>: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('form_id'); ?>"><?php _e('Select Member Form', 'ARMember') ?>:</label>
            <?php if (!empty($arm_forms)): ?>
                    <select name="<?php echo $this->get_field_name('form_id'); ?>" id="" class="" style="width:100%;" onChange="arm_update_widget_form_type(this);">
                        <option value=""><?php _e('Select Form', 'ARMember'); ?></option>
                    <?php
                    foreach ($arm_forms as $form) {
                        ?>
                            <option value="<?php echo $form['arm_form_id']; ?>" <?php selected($form_id, $form['arm_form_id']); ?> data-type="<?php echo $form['arm_form_type'] ?>"><?php echo strip_tags(stripslashes($form['arm_form_label'])) . ' &nbsp;(ID: ' . $form['arm_form_id'] . ')'; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php endif; ?>
            </p>
            <p id="arm_logged_in_message" style="<?php echo ($form_type == 'change_password') ? 'display:none;' : ''; ?>">
                <label for="<?php echo $this->get_field_id('logged_in_message'); ?>"><?php _e('Logged in Message', 'ARMember'); ?></label>
                <input type="text" name="<?php echo $this->get_field_name('logged_in_message'); ?>" id="" class="" style="width:100%;" value="<?php echo $logged_in_msg ?>" />
            </p>
            <?php
        }

        public function update($new_instance, $old_instance) {
            $instance = array();
            $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
            $instance['form_id'] = !empty($new_instance['form_id']) ? $new_instance['form_id'] : 0;
            $instance['logged_in_message'] = isset($new_instance['logged_in_message']) ? $new_instance['logged_in_message'] : __('You are already logged in.','ARMember');
            return $instance;
        }

        function scripts() {
            global $wp, $wpdb, $ARMember, $arm_ajaxurl, $arm_slugs;
            if (is_active_widget(false, false, $this->id_base, true)) {
                $ARMember->set_front_css(true);
                $ARMember->set_front_js(true);
            }
        }

    }

    if (class_exists('WP_Widget')) {

        function arm_register_forms_widgets() {
            register_widget('ARMwidgetForm');
        }

        add_action('widgets_init', 'arm_register_forms_widgets');
    }
}