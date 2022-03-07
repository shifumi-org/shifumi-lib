<?php 
if (!class_exists('ARM_modal_view_in_menu'))
{
    class ARM_modal_view_in_menu
    {
        function __construct()
        {
			global  $all_child_array, $all_items_array, $all_parent_array;
			$all_child_array = array();
            $all_items_array = array();
			$all_parent_array = array();

            add_action( 'admin_head-nav-menus.php', array($this, 'arm_add_nav_menu_metabox'), 10 );
            if( !isset($_GET['uxb_iframe'] ) ){
                add_filter('wp_nav_menu',array($this,'arm_wp_loaded_walker_menu'),10,2);
            }
			
			add_filter('wp_nav_menu',array($this,'arm_main_hook_for_exclude'),11,2);
add_action('wp_footer', array($this, 'arm_nav_menu_add_javascript'));
            //add_action('wp_footer',array($this,'arm_add_modal_popups_after_theme_loaded'));

            
            add_action('check_admin_referer', array($this, 'logout_without_confirm'), 10, 2);
            add_filter( 'wp_nav_menu_objects', array($this, 'arm_exclude_menu_items'), 11, 3 );
			
	    add_filter( 'wp_nav_menu_objects', array($this, 'arm_exclude_menu_items_2'), 100, 3 );

            /* Custom Field for Wordpress Menu Item */
            add_action('wp_update_nav_menu_item',array($this,'arm_add_nav_menu_meta_box'),10,3);
            add_filter('wp_setup_nav_menu_item',array($this,'arm_setup_nav_menu_item'));
            //add_action('admin_footer',array($this,'arm_edit_nav_menu'),10);
            add_action('wp_ajax_arm_get_post_meta_for_menu',array($this,'arm_get_post_meta_for_menu'));
            //add_action('init', array($this,'logout_from_menu_link'));
		}

function get_nav_menu_item_children( $parent_id, $nav_menu_items, $depth = true ) {
	global $all_parent_array;
    $nav_menu_item_list = array();
    $all_parent_array[] = $parent_id;
    foreach ( (array) $nav_menu_items as $nav_menu_item ) {
        if ( $nav_menu_item->menu_item_parent == $parent_id ) {
            $nav_menu_item_list[] = $nav_menu_item;
            if ( $depth ) {
                if ( $children = $this->get_nav_menu_item_children( $nav_menu_item->ID, $nav_menu_items ) )
                    $nav_menu_item_list = array_merge( $nav_menu_item_list, $children );
                }
            }
    }
    return $nav_menu_item_list;
}

 function arm_exclude_menu_items_2( $sorted_menu_objects, $args ) {
	  global $arm_member_forms,$all_child_array,$all_items_array,$all_parent_array;
        $child_parent_array = array_merge( $all_parent_array, array_unique($all_child_array) );
	   foreach ($sorted_menu_objects as $key => $menu_object) {
                $url = $menu_object->url;
                 $menu_id = $menu_object->ID;
                 $arm_hide_show_val = get_post_meta($menu_id,'arm_is_hide_show_after_login',true);
                if($arm_hide_show_val != ''){
                if(in_array($menu_id,$child_parent_array)){
                    unset($sorted_menu_objects[$key]);
                }
            }
	   }
	    return $sorted_menu_objects;
 }

function arm_main_hook_for_exclude($nav_menu,$args)
 {
     global $all_items_array,$all_child_array;
     foreach($all_child_array as $k=>$y)
     {
        foreach($all_items_array as $all_itme => $val){
            if($y ==  $val){
                unset($all_items_array[$all_itme]);
            }
        }
    }
    return $nav_menu;
 }
        function arm_exclude_menu_items( $sorted_menu_objects, $args ) {
            global $arm_member_forms,$all_child_array,$all_items_array,$all_parent_array;
            $show = '';
            $arm_logout_url = wp_login_url().'?action=logout';
            foreach ($sorted_menu_objects as $key => $menu_object) {
                $url = $menu_object->url;
                $menu_id = $menu_object->ID;
				$all_items_array[] = $menu_id;
                $arm_hide_show = get_post_meta($menu_id,'arm_is_hide_show_after_login',true);
                if($arm_hide_show == ''){
                    $arm_hide_show = 'show_to_all';
                }
                if($arm_hide_show != 'show_to_all'){
                    if( !is_user_logged_in() ){
                        
                        if($arm_hide_show == 'show_before_login'){
                            $show = 1;
                        }
                        else if($arm_hide_show == 'show_after_login'){
                            $show = 0;
                        }
                    }
                    else{
                        if($arm_hide_show == 'show_before_login'){
                            $show = 0;
                        }
                        else if($arm_hide_show == 'show_after_login'){
                            $show = 1;
                        }
                    }
                }
                if(($arm_hide_show != 'show_to_all') && ($show == 0)){
                    $all_child_array_temp = $this->get_nav_menu_item_children($menu_id, $sorted_menu_objects);
                }
                    if(!empty($all_child_array_temp)){
                        foreach($all_child_array_temp as $key_child => $child){
						   $all_child_array[] = $child->ID;
                            if($child->ID == $menu_id)
                            {
                                //unset child from sorted object
                                //unset($sorted_menu_objects[$key]);
                            }
                        }
                    }
                if( !is_admin() ){
                    switch($arm_hide_show){
                    }
                }
                if(is_user_logged_in()){
                    if($url == $arm_logout_url){
                        $menu_object->url = wp_logout_url( ARM_HOME_URL );
                    }
                }
            }
            return $sorted_menu_objects;
        }

        function logout_without_confirm($action, $result)
        {
            /**
             * Allow logout without confirmation
             */
            if ($action == "log-out" && !isset($_GET['_wpnonce'])) {
                $redirect_to = ARM_HOME_URL;
                $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
                wp_redirect($location);
                exit;
            }
        }
        function logout_from_menu_link()
        {
            $arm_action = isset($_REQUEST['arm_action']) ? $_REQUEST['arm_action'] : '';
            $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : ARM_HOME_URL;
            if ($arm_action == "logout") {
                //$location = wp_logout_url($redirect_to);
                $redirect_to = str_replace('&amp;', '&', $redirect_to);
                //wp_redirect($location);
                //wp_logout();

                wp_clear_auth_cookie();
                do_action('wp_logout');
                nocache_headers();
                wp_redirect($redirect_to);
                exit;
            }
        }
        function arm_add_nav_menu_metabox(){
            add_meta_box( 'armformnav', __( 'ARMember Forms','ARMember' ), array($this, 'arm_from_menu_metabox'), 'nav-menus', 'side', 'default' );
            add_meta_box( 'armsetupnav', __( 'ARMember Configure Plan & Setup','ARMember' ), array($this, 'arm_setup_menu_metabox'), 'nav-menus', 'side', 'default' );
            add_meta_box( 'armlogout', __( 'ARMember Logout','ARMember' ), array($this, 'arm_logout_menu_metabox'), 'nav-menus', 'side', 'default' );
            ?>
            <style type="text/css">
                .armformnav .accordion-section-title.hndle, .armlogout .accordion-section-title.hndle, .armsetupnav .accordion-section-title.hndle,
                .armformnav.open .accordion-section-title.hndle, .armlogout.open .accordion-section-title.hndle, .armsetupnav.open .accordion-section-title.hndle {
                    background: #005aee !important;
                    background-color: #005aee !important;
                    border-top: 1px solid #ffffff !important;
                    color: #ffffff;
                    margin: -6px 0 0;
                    padding-left: 34px;
                    position: relative;
                }
                .armformnav .accordion-section-title.hndle:focus,
                .armlogout .accordion-section-title.hndle:focus,
                .armsetupnav .accordion-section-title.hndle:focus,
                .armformnav .accordion-section-title.hndle:hover,
                .armlogout .accordion-section-title.hndle:hover,
                .armsetupnav .accordion-section-title.hndle:hover
                {
                    background-color: #005aee;
                    color: white;
                    margin: -6px 0 0;
                    position: relative;
                }
                .armformnav .accordion-section-title.hndle::before{
                    background-image: url(<?php echo MEMBERSHIP_IMAGES_URL.'/logo_navmenu_white.png' ?>);
                    height: 20px;
                    width: 20px;
                    content: " ";
                    position: absolute;
                    left: 8px;
                }
                .armlogout .accordion-section-title.hndle::before{
                    background-image: url(<?php echo MEMBERSHIP_IMAGES_URL.'/logo_navmenu_white.png' ?>);
                    height: 20px;
                    width: 20px;
                    content: " ";
                    position: absolute;
                    left: 8px;
                }
                .armsetupnav .accordion-section-title.hndle::before{
                    background-image: url(<?php echo MEMBERSHIP_IMAGES_URL.'/logo_navmenu_white.png' ?>);
                    height: 20px;
                    width: 20px;
                    content: " ";
                    position: absolute;
                    left: 8px;
                    top: 11px;
                }
                .armformnav .accordion-section-title::after, .armlogout .accordion-section-title::after, .armsetupnav .accordion-section-title::after{
                    color: #fff !important;
                }
                #menu-settings-column .armformnav .inside,
                #menu-settings-column .armlogout .inside,
                #menu-settings-column .armsetupnav .inside{
                    margin: 0;
                }
                .arm_color_red {
                    color: #ff0000 !important ;
                }
            </style>
        <?php }
        function arm_from_menu_metabox($object)
        {
            global $nav_menu_selected_id,$wpdb,$ARMember,$arm_member_forms;
            // Create an array of objects that imitate Post objects
            $form_items = array();
            $registration_forms = $wpdb->get_results("SELECT `arm_form_id`, `arm_form_label`, `arm_form_slug`, `arm_is_default`, `arm_form_updated_date` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`='registration' ORDER BY `arm_form_id` DESC", ARRAY_A);
            $otherForms = $arm_member_forms->arm_get_member_form_sets();
            if(!empty($otherForms)):
                foreach($otherForms as $setID => $formSet):
                    if(!empty($formSet)):
						$formSetValues = array_values($formSet);
						$firstForm = array_shift($formSetValues);
						reset($formSet);
                        foreach($formSet as $_form):
                            $_flabel = wp_strip_all_tags($_form['arm_form_label'].' (Form ID: '.$_form["arm_form_id"].')');
                            $o_navigation_link = ARM_HOME_URL. "?armaction=arm_modal_view_menu&id=";
                            $o_navigation_link .= $_form['arm_form_id'];
                            $o_navigation_link .= "&popup_width=700&popup_height=auto&overlay=0.6&modal_bgcolor=#000000&nav_menu=1";
                            $form_items[] = (object) array(
                                'ID' => 1,
                                'db_id' => 0,
                                'menu_item_parent' => 0,
                                'object_id' => $o_navigation_link,
                                'post_parent' => 0,
                                'type' => 'custom',
                                'object' => 'arm-form-slug',
                                'type_label' => 'ARMember Plugin',
                                'title' => $_flabel,
                                'url' => $o_navigation_link,
                                'target' => '',
                                'attr_title' => '',
                                'description' => '',
                                'classes' => array(),
                                'xfn' => '',
                            ); 
                        endforeach;
                    endif;
                endforeach;
            endif;
            if(!empty($registration_forms)){
                foreach($registration_forms as $_form):
                    $_flabel = wp_strip_all_tags($_form['arm_form_label'].' (Form ID: '.$_form["arm_form_id"].')');
                    $_fid = $_form['arm_form_id'];
                    $r_navigation_link = ARM_HOME_URL. "?armaction=arm_modal_view_menu&id=";
                    $r_navigation_link .= $_fid;
                    $r_navigation_link .= "&popup_width=700&popup_height=auto&overlay=0.6&modal_bgcolor=#000000&nav_menu=1";
                    $form_items[] = (object) array(
                    'ID' => 1,
                    'db_id' => 0,
                    'menu_item_parent' => 0,
                    'object_id' => $r_navigation_link,
                    'post_parent' => 0,
                    'type' => 'custom',
                    'object' => 'arm-form-slug',
                    'type_label' => 'ARMember Plugin',
                    'title' => $_flabel,
                    'url' => $r_navigation_link,
                    'target' => '',
                    'attr_title' => '',
                    'description' => '',
                    'classes' => array(),
                    'xfn' => '',
                ); 
                endforeach;
            $db_fields = false;
            // If your links will be hieararchical, adjust the $db_fields array bellow
            if ( false ) {
                $db_fields = array( 'parent' => 'parent', 'id' => 'post_parent' );
            }
            $walker = new Walker_Nav_Menu_Checklist( $db_fields );
            $removed_args = array(
                'action',
                'customlink-tab',
                'edit-menu-item',
                'menu-item',
                'page-tab',
                '_wpnonce',
            );
            ?>
            <div id="arm-login-links" class="loginlinksdiv posttypediv">
                <div><?php _e("<p class='arm_color_red'>NOTE: This feature will only work with those themes which has support of wordpress' navigation menu core hooks.</p>", 'ARMember');?></div>
                <p><?php _e("This navigation menu link will open Armember form in Modal Window.", 'ARMember');?></p>
                <div id="tabs-panel-arm-login-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
                    <ul id="arm-login-linkschecklist" class="list:arm-login-links categorychecklist form-no-clear">
                        <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $form_items ), 0, (object) array( 'walker' => $walker ) ); ?>
                    </ul>
                </div>
                <p class="button-controls">
                    <span class="list-controls">
                        <a href="<?php
                            echo esc_url(add_query_arg(
                                array(
                                    'my-plugin-all' => 'all',
                                    'selectall' => 1,
                                ),
                                remove_query_arg( $removed_args )
                            ));
                        ?>#armformnav" class="select-all"><?php _e( 'Select All','ARMember' ); ?></a>
                    </span>

                    <span class="add-to-menu">
                        <input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu','ARMember' ); ?>" name="add-arm-login-links-menu-item" id="submit-arm-login-links" />
                        <span class="spinner"></span>
                    </span>
                </p>
            </div>
<?php
        }// if completed
        else{
            echo 'No items.';
        }
    }
    function arm_setup_menu_metabox($object)
    {
        global $nav_menu_selected_id,$ARMember,$wpdb;
        $setup_items = array();
        // Create an array of objects that imitate Post objects
        $setup_data = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_membership_setup. "`", ARRAY_A);
        if (!empty($setup_data)) {
            foreach($setup_data as $_setup){
                    $_slabel = wp_strip_all_tags($_setup['arm_setup_name'].' (Setup ID: '.$_setup['arm_setup_id'].')');
                    $_sid = $_setup['arm_setup_id'];
                    $s_navigationLink = ARM_HOME_URL. "?armaction=arm_modalmembership_setup&id=";
                    $s_navigationLink .= $_sid;
                    $s_navigationLink .= "&popup_width=800&popup_height=auto&overlay=0.6&modal_bgcolor=#000000";
                    $setup_items[] = (object) array(
                    'ID' => 1,
                    'db_id' => 0,
                    'menu_item_parent' => 0,
                    'object_id' => $s_navigationLink,
                    'post_parent' => 0,
                    'type' => 'custom',
                    'object' => 'arm-setup-slug',
                    'type_label' => 'ARMember Plugin',
                    'title' => $_slabel,
                    'url' => $s_navigationLink,
                    'target' => '',
                    'attr_title' => '',
                    'description' => '',
                    'classes' => array(),
                    'xfn' => '',
                ); 
            }
        
        $db_fields = false;
        // If your links will be hieararchical, adjust the $db_fields array bellow
        if ( false ) {
            $db_fields = array( 'parent' => 'parent', 'id' => 'post_parent' );
        }
        $walker = new Walker_Nav_Menu_Checklist( $db_fields );
        $removed_args = array(
            'action',
            'customlink-tab',
            'edit-menu-item',
            'menu-item',
            'page-tab',
            '_wpnonce',
        ); ?>
        <div id="arm-login-links-setup" class="loginlinksdiv posttypediv">
            <div><?php _e("<p class='arm_color_red'>NOTE: This feature will only work with those themes which has support of wordpress' navigation menu core hooks.</p>", 'ARMember');?></div>
            <p><?php _e("This navigation menu link will open Armember Setup Form in Modal Window.", 'ARMember');?></p>
            <div id="tabs-panel-arm-login-links-setup-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
                <ul id="arm-login-links-setupchecklist" class="list:arm-login-links-setup categorychecklist form-no-clear">
                    <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $setup_items ), 0, (object) array( 'walker' => $walker ) ); ?>
                </ul>
            </div>
            <p class="button-controls">
                <span class="list-controls">
                    <a href="<?php
                        echo esc_url(add_query_arg(
                            array(
                                'my-plugin-all' => 'all',
                                'selectall' => 1,
                            ),
                            remove_query_arg( $removed_args )
                        ));
                    ?>#armsetupnav" class="select-all"><?php _e( 'Select All','ARMember' ); ?></a>
                </span>

                <span class="add-to-menu">
                    <input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu','ARMember' ); ?>" name="add-arm-login-links-setup-menu-item" id="submit-arm-login-links-setup" />
                    <span class="spinner"></span>
                </span>
            </p>
        </div>    
<?php } //if completed
    else{
        echo 'No items.';
    }
}
function arm_logout_menu_metabox($object)
        {
            global $nav_menu_selected_id,$wpdb,$ARMember,$arm_member_forms;
            // Create an array of objects that imitate Post objects
            $form_items = array();
            
                            $_Lolabel = "Logout";
                            //$lo_navigation_link = wp_login_url().'?action=logout';
                            $lo_navigation_link = add_query_arg(array("arm_action" => "logout"), ARM_HOME_URL);
                            
                            $form_items[] = (object) array(
                                'ID' => 1,
                                'db_id' => 0,
                                'menu_item_parent' => 0,
                                'object_id' => $lo_navigation_link,
                                'post_parent' => 0,
                                'type' => 'custom',
                                'object' => 'arm-form-slug',
                                'type_label' => 'ARMember Plugin',
                                'title' => $_Lolabel,
                                'url' => $lo_navigation_link,
                                'target' => '',
                                'attr_title' => '',
                                'description' => '',
                                'classes' => array(),
                                'xfn' => '',
                            ); 
            $db_fields = false;
            // If your links will be hieararchical, adjust the $db_fields array bellow
            if ( false ) {
                $db_fields = array( 'parent' => 'parent', 'id' => 'post_parent' );
            }
            $walker = new Walker_Nav_Menu_Checklist( $db_fields );
            $removed_args = array(
                'action',
                'customlink-tab',
                'edit-menu-item',
                'menu-item',
                'page-tab',
                '_wpnonce',
            ); 
            ?>
            <div id="arm-logout-links" class="loginlinksdiv posttypediv">
                <div><?php _e("<p class='arm_color_red'>NOTE: This feature will only work with those themes which has support of wordpress' navigation menu core hooks.</p>", 'ARMember');?></div>
                <p><?php _e("This navigation menu link is to set Logout Link.", 'ARMember');?></p>
                <div id="tabs-panel-arm-logout-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
                    <ul id="arm-logout-linkschecklist" class="list:arm-logout-links categorychecklist form-no-clear">
                        <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $form_items ), 0, (object) array( 'walker' => $walker ) ); ?>
                    </ul>
                </div>
                <p class="button-controls">
                    <span class="add-to-menu">
                        <input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu','ARMember' ); ?>" name="add-arm-logout-links-menu-item" id="submit-arm-logout-links" />
                        <span class="spinner"></span>
                    </span>
                </p>
            </div>    
<?php }

		function arm_nav_menu_add_javascript() {			
			?>
			<script data-cfasync="false" type="text/javascript">
			function arm_open_modal_box_in_nav_menu(menu_id, form_id) {
                           
				jQuery(".arm_nav_menu_link_" + form_id).find("." + form_id).trigger("click");
				return false;
			}
			</script>
			<?php
		}

        function arm_add_modal_popups_after_theme_loaded(){
            global $arm_popup_modal_elements, $arm_inner_form_modal;
            if( !is_admin() ){
                if( is_array($arm_popup_modal_elements) && !empty($arm_popup_modal_elements) ){
                    foreach( $arm_popup_modal_elements as $key => $arm_modal_popup ){
                        echo do_shortcode( $arm_modal_popup );
                    }
                }
                if( is_array($arm_inner_form_modal) && count($arm_inner_form_modal) > 0 ){
                   
                    foreach($arm_inner_form_modal as $modal_popup){
                        echo do_shortcode($modal_popup);
                    }
                }
            }
        }
        function arm_wp_loaded_walker_menu($nav_menu,$args){
            global $ARMember,$bpopup_loaded,$arm_popup_modal_elements;
            preg_match('/armaction=(arm_modal_view_menu|arm_modalmembership_setup)/',$nav_menu,$matches);
            if( count( $matches ) > 0 ){
                $dom = new DOMDocument;
                if (extension_loaded('mbstring')) {
                    @$dom->loadHTML(mb_convert_encoding($nav_menu, 'HTML-ENTITIES', 'UTF-8'));
                } else {
                    @$dom->loadHTML(htmlspecialchars_decode(utf8_decode(htmlentities($nav_menu, ENT_COMPAT, 'utf-8', false))));
                }
                $n = new DOMXPath($dom);
                $new_menu = '';
                $anchor_tag = $dom->getElementsByTagName('a');
                foreach( $anchor_tag as $tag ){
                    $href = $tag->getAttribute('href');
                    $echo = "";
                    if( preg_match('/armaction=(arm_modal_view_menu|arm_modalmembership_setup)/',$href) ){
                        $menu_id = '';
                        /* changes for notice warning need to confirm */
                        if(isset($args->menu->term_id)) {
                            $menu_id = $args->menu->term_id;
                        }
                        if (!is_admin()) {
                            $ARMember->set_front_css(true);
                            $ARMember->set_front_js(true);
                            do_action('arm_enqueue_js_css_from_outside');
                        }
                        $arm_menu_array = array();
                        $arm_menu_elems = explode("&", str_replace('&amp;', '&', $href));
                        if (!empty($arm_menu_elems)) {
                            foreach ($arm_menu_elems as $arm_menu_elem) {
                                if (!empty($arm_menu_elem)) {
                                    $arm_link_pera = explode("=", $arm_menu_elem);
                                    $arm_menu_array[$arm_link_pera[0]] = $arm_link_pera[1];
                                }
                            }
                        }
                        if (!empty($arm_menu_array)) {
                            if (array_key_exists('id', $arm_menu_array) && !empty($arm_menu_array['id'])) {
                                $formAttr = " id='".$arm_menu_array['id']."' ";
                                $formRandomID = $arm_menu_array['id'] . arm_generate_random_code(8);
                                $formAttr .= " link_class=\"arm_form_link_$formRandomID\"";
                                $formAttr .= " link_type=\"link\" link_title=\"&nbsp;\" link_css=\"\" link_hover_css=\"\"";
                                $formAttr .= " popup=\"true\"";
                                if (isset($arm_menu_array['popup_height']) && !empty($arm_menu_array['popup_height'])) {
                                    $formAttr .= " popup_height=\"".$arm_menu_array['popup_height']."\"";
                                }
                                if (isset($arm_menu_array['popup_width']) && !empty($arm_menu_array['popup_width'])) {
                                    $formAttr .= " popup_width=\"".$arm_menu_array['popup_width']."\"";
                                }
                                if (isset($arm_menu_array['overlay']) && !empty($arm_menu_array['overlay'])) {
                                    $formAttr .= " overlay=\"".$arm_menu_array['overlay']."\"";
                                }
                                if (isset($arm_menu_array['modal_bgcolor']) && !empty($arm_menu_array['modal_bgcolor'])) {
                                    $formAttr .= " modal_bgcolor=\"".$arm_menu_array['modal_bgcolor']."\"";
                                }
                                if (isset($arm_menu_array['nav_menu']) && $arm_menu_array['nav_menu'] == 1 ) {
                                    $formAttr .= " nav_menu=\"1\"";
                                }
                                if (!empty($arm_menu_array['assign_default_plan'])) {
                                    $formAttr .= " assign_default_plan=\"".$arm_menu_array['assign_default_plan']."\"";
                                }
                                $onClick = "arm_open_modal_box_in_nav_menu('$menu_id','arm_form_link_".$formRandomID."');return false;";
                                $arm_data_id = "arm_form_link_".$formRandomID;
                                $shortcode = "[arm_form ".$formAttr."]";
                                if( preg_match('/armaction=arm_modalmembership_setup/',$href)){
                                    if (isset($arm_menu_array['hide_plans']) ) {
                                        $formAttr .= " hide_plans=\"".$arm_menu_array['hide_plans']."\"";
                                    }
                                    if (isset($arm_menu_array['hide_title']) ) {
                                        $formAttr .= " hide_title=\"".$arm_menu_array['hide_title']."\"";
                                    }
                                    if (!empty($arm_menu_array['subscription_plan']) ) {
                                        $formAttr .= " subscription_plan=\"".$arm_menu_array['subscription_plan']."\"";
                                    }
                                    if (!empty($arm_menu_array['payment_duration']) ) {
                                        $formAttr .= " payment_duration=\"".$arm_menu_array['payment_duration']."\"";
                                    }
                                    $shortcode = "[arm_setup $formAttr]";
                                }
                                $echo = "<div id=\"arm_nav_menu_link_".$menu_id."\" class=\"arm_nav_menu_form_container arm_nav_menu_link_".$menu_id." arm_nav_menu_link_arm_form_link_".$formRandomID."\" style=\"display:none;\">";
                                $echo .= $shortcode;
                                $echo .= "</div>";
                                $arm_popup_modal_elements[$formRandomID] = $echo;
                            }
                            $tag->setAttribute('href','#');
                            $tag->setAttribute('onClick',$onClick);
                            $tag->setAttribute('arm-data-id',$arm_data_id);

                        }
                        $bpopup_loaded = 1;
                    }
                    $new_menu = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));
                }
                $nav_menu = $new_menu;
            }
            return $nav_menu;
        }

        function arm_add_nav_menu_meta_box($menu_id, $menu_item_db_id, $args) {
            global $ARMember;

            if (isset($_REQUEST['arm_is_hide_show_after_login'])) {
                if (is_array($_REQUEST['arm_is_hide_show_after_login'])) {
                    $custom_value = isset($_REQUEST['arm_is_hide_show_after_login'][$menu_item_db_id]) ? $_REQUEST['arm_is_hide_show_after_login'][$menu_item_db_id] : 'always_show';
                    update_post_meta($menu_item_db_id, 'arm_is_hide_show_after_login', $custom_value);
                    if (!empty($_REQUEST['arm_access_rule_menu']) && $custom_value == 'show_after_login') {
                        $menu_rules = isset($_REQUEST['arm_access_rule_menu'][$menu_item_db_id]) ? $_REQUEST['arm_access_rule_menu'][$menu_item_db_id] : array();
                        if (!empty($menu_rules) && count($menu_rules) > 0) {
                            delete_post_meta($menu_item_db_id, 'arm_protection');
                            delete_post_meta($menu_item_db_id, 'arm_access_plan');
							add_post_meta($menu_item_db_id, 'arm_access_plan', '0');
							
                            foreach ($menu_rules as $plan_id) {
                                delete_post_meta($menu_item_db_id, 'arm_access_plan', $plan_id);
                                add_post_meta($menu_item_db_id, 'arm_access_plan', $plan_id);
                            }
                        } else {
                            delete_post_meta($menu_item_db_id, 'arm_protection');
                            delete_post_meta($menu_item_db_id, 'arm_access_plan');
                        }
                    } else {
                        delete_post_meta($menu_item_db_id, 'arm_protection');
                        delete_post_meta($menu_item_db_id, 'arm_access_plan');
                    }
                    if (!isset($_REQUEST['arm_access_rule_menu'])) {
                        delete_post_meta($menu_item_db_id, 'arm_protection');
                        delete_post_meta($menu_item_db_id, 'arm_access_plan');
                    }
                }
            }
        }

        function arm_setup_nav_menu_item($menu_item){
            $menu_item->custom = get_post_meta($menu_item->ID,'arm_is_hide_show_after_login',true);
            return $menu_item;
        }

        function arm_edit_nav_menu() {
            global $pagenow, $arm_subscription_plans;
            
            if ($pagenow == 'nav-menus.php') {
                $all_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans('', '', true);
            $all_plan_filtered = array();
            if (!empty($all_plans)) {
                $i = 0;
                foreach ($all_plans as $plan) {
                    $all_plan_filtered[$i]['id'] = $plan['arm_subscription_plan_id'];
                    $all_plan_filtered[$i]['name'] = $plan['arm_subscription_plan_name'];
                    $i++;
                }
            }
                
                ?>
                <input type="hidden" id="armember_all_plan_lists" value="<?php echo htmlspecialchars(json_encode($all_plan_filtered)); ?>" />
                <style type='text/css'>
                    .arm-menu-item-hide-show{
                        float:left;
                        width:100%;
                        background: #005aee;
                        color:#ffffff;
                        position: relative;
                        padding:5px 0 5px 10px;
                        left:-10px;
                    }
                    .arm-menu-item-hide-show:before{
                        background-image: url(<?php echo MEMBERSHIP_IMAGES_URL.'/logo_navmenu_white.png' ?>);
                        height: 20px;
                        width: 20px;
                        content: " ";
                        position: absolute;
                        right: 8px;
                        top:5px;
                        background-repeat: no-repeat;
                    }.arm_font_size_16 {
                        font-size: 16px !important;
                    }
                </style>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        arm_add_menu_custom_meta_item();
                    });
                    function display_access_rules_for_this_menu(display, menu_id) {
                        if (display) {
                            jQuery('#arm_access_rule_for_menu-' + menu_id).show('slow');
                        } else {
                            jQuery('#arm_access_rule_for_menu-' + menu_id).hide('slow');
                        }
                    }
                    function create_menu_item_meta_box(menu_item_type) {
                        var meta_box_html = "";
                        meta_box_html += '<p class="field-custom arm-menu-item-hide-show description-wide">';
                        meta_box_html += '<label for="edit-menu-item-custom-[ARM_MENU_ITEM_ID]">';
                        meta_box_html += '<b><?php echo addslashes(__('Hide / Show Menu Item', 'ARMember')); ?></b><br/>';
                        meta_box_html += '<input type="radio" id="arm_is_hide_show_to_all-[ARM_MENU_ITEM_ID]" class="widefat code arm_is_hide_show_after_login" name="arm_is_hide_show_after_login[[ARM_MENU_ITEM_ID]]" value="show_to_all" onchange="display_access_rules_for_this_menu(false,[ARM_MENU_ITEM_ID])">';
                        meta_box_html += '<label for="arm_is_hide_show_to_all-[ARM_MENU_ITEM_ID]"><?php echo addslashes(__('Display to All', 'ARMember')); ?></label>&nbsp;';
                        meta_box_html += '<input type="radio" id="arm_is_hide_show_loggedin_user-[ARM_MENU_ITEM_ID]" class="widefat code arm_is_hide_show_after_login" name="arm_is_hide_show_after_login[[ARM_MENU_ITEM_ID]]" value="show_after_login" onchange="display_access_rules_for_this_menu(true,[ARM_MENU_ITEM_ID])" >';
                        meta_box_html += '<label for="arm_is_hide_show_loggedin_user-[ARM_MENU_ITEM_ID]"><?php echo addslashes(__('Only Logged in User', 'ARMember')); ?></label>&nbsp;';
                        meta_box_html += '<input type="radio" id="arm_is_hide_show_non_loggedin_user-[ARM_MENU_ITEM_ID]" class="widefat code arm_is_hide_show_after_login" name="arm_is_hide_show_after_login[[ARM_MENU_ITEM_ID]]" value="show_before_login" onchange="display_access_rules_for_this_menu(false,[ARM_MENU_ITEM_ID])">';
                        meta_box_html += '<label for="arm_is_hide_show_non_loggedin_user-[ARM_MENU_ITEM_ID]"><?php echo addslashes(__('Only non logged in User', 'ARMember')); ?></label>&nbsp;';
                        meta_box_html += '</label>';
                        meta_box_html += '<br/>';

                        meta_box_html += '<span id="arm_access_rule_for_menu-[ARM_MENU_ITEM_ID]" style="display:none;padding-top:10px;">';
                        meta_box_html += '<b class="arm_font_size_16"><?php echo addslashes(__('Select Plan(s) whose users can access this menu item.','ARMember')); ?></b><br/>';
                        var all_plans = jQuery.parseJSON(jQuery('#armember_all_plan_lists').val());
                        jQuery(all_plans).each(function (i) {
                            meta_box_html += '<input type="checkbox" id="arm_access_rule_for_menu-[ARM_MENU_ITEM_ID]-' + all_plans[i]['id'] + '" name="arm_access_rule_menu[[ARM_MENU_ITEM_ID]][]" value="' + all_plans[i]['id'] + '" />';
                            meta_box_html += '<label for="arm_access_rule_for_menu-[ARM_MENU_ITEM_ID]-' + all_plans[i]['id'] + '">';
                            meta_box_html += all_plans[i]['name'];
                            meta_box_html += '</label>';
                            meta_box_html += '<br/>';
                        });
                        meta_box_html += '</span>';

                        meta_box_html += '</p>';
                        return meta_box_html;
                    }
                    jQuery(document).ajaxComplete(function (event, xhr, settings) {

                        if (settings.data.match(/action=add-menu-item/) !== null && settings.data.match(/action=add-menu-item/).length > -1) {
                            arm_add_menu_custom_meta_item();
                        }
                    });
                    function arm_add_menu_custom_meta_item(){
                        var menu_item_ids = new Array();
                        jQuery('.arm-menu-item-hide-show').remove();
                        jQuery('ul#menu-to-edit > li').each(function(){
                            var id = jQuery(this).attr('id');
                            var menu_item_id = id.replace('menu-item-','');
                            var obj = jQuery(this).find('.menu-item-settings');
                            var menu_item_type = jQuery(this).find('.item-type').text().toLowerCase();

                            var controls = create_menu_item_meta_box(menu_item_type);
                            var new_text = controls.replace(/(\[ARM_MENU_ITEM_ID\])/g, menu_item_id);
                            var control_html = jQuery.parseHTML(new_text);
                            obj.find('.menu-item-actions').before(control_html);
                            menu_item_ids.push(menu_item_id);
                        });
                        if( menu_item_ids.length > 0 ){
                            var item_ids = JSON.stringify(menu_item_ids);
                            jQuery.ajax({
                                url:__ARMAJAXURL,
                                method:'POST',
                                dataType:'json',
                                data:'action=arm_get_post_meta_for_menu&ids='+item_ids,
                                success:function(response){
                                    if( response.error == false ){
                                        for(var id in response.res){
                                            switch(response.res[id]){
                                                case 'always_show':
                                                    jQuery('input#arm_is_hide_show_loggedin_user-' + id).prop('checked', false);
                                                    jQuery('input#arm_is_hide_show_non_loggedin_user-' + id).prop('checked', false);
                                                    jQuery('input#arm_is_hide_show_to_all-' + id).prop('checked', true);
                                                    display_access_rules_for_this_menu(false, id);
                                                    break;
                                                case 'show_after_login':
                                                    jQuery('input#arm_is_hide_show_non_loggedin_user-' + id).prop('checked', false);
                                                    jQuery('input#arm_is_hide_show_to_all-' + id).prop('checked', false);
                                                    jQuery('input#arm_is_hide_show_loggedin_user-' + id).prop('checked', true);
                                                    display_access_rules_for_this_menu(true, id);
                                                    break;
                                                case 'show_before_login':
                                                    jQuery('input#arm_is_hide_show_loggedin_user-' + id).prop('checked', false);
                                                    jQuery('input#arm_is_hide_show_to_all-' + id).prop('checked', false);
                                                    jQuery('input#arm_is_hide_show_non_loggedin_user-' + id).prop('checked', true);
                                                    display_access_rules_for_this_menu(false, id);
                                                    break;
                                                default:
                                                    jQuery('input#arm_is_hide_show_loggedin_user-' + id).prop('checked', false);
                                                    jQuery('input#arm_is_hide_show_non_loggedin_user-' + id).prop('checked', false);
                                                    jQuery('input#arm_is_hide_show_to_all-' + id).prop('checked', true);
                                                    display_access_rules_for_this_menu(false, id);
                                                    break;
                                            }
                                        }
                                        for (var id in response.access_rule) {
                                            if (response.access_rule[id].length > 0) {
                                                var rules = response.access_rule[id];
                                                for (var x in rules) {
                                                    jQuery('#arm_access_rule_for_menu-' + id + '-' + rules[x]).prop('checked', true);
                                                }
                                            }
                                        }
                                    } else {
                                    }
                                }
                            });
                        }
                    }
                </script>
                <?php
            }
        }

        function arm_get_post_meta_for_menu(){
            $response = array();
            if( isset($_REQUEST['ids']) && !empty($_REQUEST['ids']) ){
                $response['error'] = false;
                $response['res'] = array();
                $ids = json_decode(stripslashes_deep($_REQUEST['ids']));

                $global_option = get_option('arm_default_rules');
                $global_option = !empty($global_option) ? maybe_unserialize($global_option) : array();
                
                foreach ($ids as $key => $menu_id) {
                    
                    $menu_hide_show = get_post_meta($menu_id, 'arm_is_hide_show_after_login', true);
                    $menu_hide_show_plan = !empty(get_post_meta($menu_id, 'arm_access_plan', false)) ? get_post_meta($menu_id, 'arm_access_plan', false) : array();

                    if(empty($menu_hide_show)) {
                        
                        $nav_menu = isset($global_option['nav_menu']) ? $global_option['nav_menu'] : "";
                        if(empty($nav_menu)){
                            $menu_hide_show = "always_show";
                        } else {
                            $menu_hide_show = "show_after_login";
                            array_push($menu_hide_show_plan, $nav_menu);
                        }

                    }
                    $response['res'][$menu_id] = $menu_hide_show;
                    $response['access_rule'][$menu_id] = $menu_hide_show_plan;
                }
            } else {
                $response['error'] = true;
            }
            echo json_encode($response);
            die();
        }
	}
}
global $arm_modal_view_in_menu;
$arm_modal_view_in_menu = new ARM_modal_view_in_menu();