<?php
if (!class_exists('Spam_Filter'))
{
	class Spam_Filter
	{
		const nonce_action = 'form_spam_filter';
		const nonce_name = 'arm_nonce_check';
		const nonce_start_time = 'form_filter_st';
		const nonce_keyboard_press = 'form_filter_kp';
		var $nonce_fields;
		function __construct()
		{
			global $wp, $wpdb,$arm_global_settings;
			
			add_shortcode('armember_spam_filters', array(&$this, 'armember_spam_filters_func'));

			$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
			$general_settings = $all_global_settings['general_settings'];
			$spam_protection = isset($general_settings['spam_protection']) ? $general_settings['spam_protection'] : '';
			if(!empty($spam_protection)){

				add_filter('armember_validate_spam_filter_fields', array(&$this, 'armember_check_spam_filter_fields'), 10, 2);
			}	
		}
		function armember_check_spam_filter_fields($validate = true,$form_key = '')
		{
			global $wp, $wpdb, $ARMember, $arm_case_types;
			$is_form_key = $arm_is_dynamic_field = $arm_is_removed_field = true;
			$ARMember->arm_session_start();
			/* Return false if session is blank. */
			if( !isset($_SESSION['ARM_FILTER_INPUT']) && @$_SESSION['ARM_VALIDATE_SCRIPT'] == true ){
				$arm_is_removed_field = false;
			}
			if( MEMBERSHIP_DEBUG_LOG == true ){
	            $arm_case_types['shortcode']['protected'] = true;
	            $arm_case_types['shortcode']['message'] = " Is session assigned => ".json_encode($_SESSION);
	            $arm_case_types['shortcode']['type'] = "spam_filter";
	            $ARMember->arm_debug_response_log('armember_check_spam_filter_fields',$arm_case_types,array(),$wpdb->last_query);
	        }
			/* Return false if form key not found */
			if( $form_key == '' || !@array_key_exists($form_key,@$_SESSION['ARM_FILTER_INPUT']) ){
				$is_form_key = false;
			}
			/* Get dynamic generated field */
			$field_name = @$_SESSION['ARM_FILTER_INPUT'][$form_key];
			if( isset($_REQUEST[$field_name]) ){
				$field_value = $_REQUEST[$field_name];
				/* Check if dynamic generated field value. Return if modified */
				if( $field_value != "" || !empty($field_value) || $field_value != NULL ){
					$arm_is_dynamic_field = false;
				}
			} else {
				$arm_is_dynamic_field = false;
			}

			$is_removed_field_exists = false;
			/* Get dynamically removed field. Return if found */
			if( isset($_REQUEST['arm_filter_input']) || isset($_POST['arm_filter_input']) || isset($_GET['arm_filter_input']) ){
				$arm_is_removed_field = false;
				$is_removed_field_exists = true;
			}

			/* Remove old keys from stored session */
			unset($_SESSION['ARM_FILTER_INPUT'][$form_key]);

			/* Check if Script is Executed. Bypass if script is not executed due to suPHP extension or blocked iframe */
			if( !isset($_SESSION['ARM_VALIDATE_SCRIPT']) || $_SESSION['ARM_VALIDATE_SCRIPT'] == false ){
				$arm_is_dynamic_field = true;
				$is_form_key = true;
			}

			$validateNonce = $validateReferer = $in_time = $is_user_keyboard = false;
			if (isset($_REQUEST) && isset($_REQUEST[self::nonce_name])) {
				$referer = $this->validateReferer();
				if ($referer['pass'] === true && $referer['hasReferrer'] === true) {
					$validateReferer = true;
				}
				/* Check Form Submission Time. */
				$in_time = $this->validateTimedFormSubmission();
				/* Check Keyboard Use */
				$is_user_keyboard = $this->validateUsedKeyboard();
			}
			$validateNonce = true;
			if( MEMBERSHIP_DEBUG_LOG == true ){
				$arm_spam_validation_logs = " is valid Nonce => ".$validateNonce;
				$arm_spam_validation_logs .= "\r\n is validate referer => ".$validateReferer;
				$arm_spam_validation_logs .= "\r\n is submit on time => ".$in_time;
				$arm_spam_validation_logs .= "\r\n is keyboard use => ".$is_user_keyboard;
				$arm_spam_validation_logs .= "\r\n is filtered key => ".$is_form_key;
				$arm_spam_validation_logs .= "\r\n is dynamic field => ".$arm_is_dynamic_field;
				$arm_spam_validation_logs .= "\r\n is removed field => ".$arm_is_removed_field;
	            $arm_case_types['shortcode']['protected'] = true;
	            $arm_case_types['shortcode']['message'] = $arm_spam_validation_logs;
	            $arm_case_types['shortcode']['type'] = "spam_filter";
	            $ARMember->arm_debug_response_log('armember_check_spam_filter_fields',$arm_case_types,array(),$wpdb->last_query);
	        }
			if ($validateNonce && $validateReferer && $in_time && $is_user_keyboard && $is_form_key && $arm_is_dynamic_field && $arm_is_removed_field ) {
				$validate = true;
			} else {
				$validate = false;
			}
			return $validate;
		}
		function armember_spam_filters_func($atts, $content = "")
		{
			global $arm_global_settings;

			$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
			$general_settings = $all_global_settings['general_settings'];
			$spam_protection = isset($general_settings['spam_protection']) ? $general_settings['spam_protection'] : '';
			if(!empty($spam_protection)){
				$defaults = array(
					'var' => '',
				);
				/* Extract Shortcode Attributes */
				$opts = shortcode_atts( $defaults, $atts, 'spam_filters' );
				extract( $opts );

				$content .= $this->add_form_fields();
			}else{
				$content='';
			}	

			return do_shortcode($content);
		}
		function add_form_fields()
		{
			$this->nonce_fields = '<input type="hidden" name="" class="kpress" value="" />';
			$this->nonce_fields .= '<input type="hidden" name="" class="stime" value="'. (time()+14921) .'" />';
			$this->nonce_fields .= '<input type="hidden" data-id="nonce_start_time" class="nonce_start_time" value="'.self::nonce_start_time.'" />';
			$this->nonce_fields .= '<input type="hidden" data-id="nonce_keyboard_press" class="nonce_keyboard_press" value="'.self::nonce_keyboard_press.'" />';
			if( function_exists('wp_nonce_field') )
			{
				$this->nonce_fields .= '<input type="hidden" name="' . self::nonce_name . '" value="' . wp_create_nonce( self::nonce_action ) . '" />';

				//wp_nonce_field( self::nonce_action, self::nonce_name, false, false );
			}
			return $this->nonce_fields;
		}
		function validateTimedFormSubmission($formContents=array())
		{
			$in_time = false;
			if(empty($formContents[self::nonce_start_time])) {
				$formContents[self::nonce_start_time] = isset($_REQUEST[self::nonce_start_time]) ? $_REQUEST[self::nonce_start_time] : '';
			}
			if(isset($formContents[self::nonce_start_time]))
			{
				$displayTime = $formContents[self::nonce_start_time] - 14921;
				$submitTime = time();
				$fillOutTime = $submitTime - $displayTime;
				/* Less than 3 seconds */
				if ($fillOutTime < 3) {
					$in_time = false;
				} else {
					$in_time = true;
				}
			}
			return $in_time;
		}
		function validateUsedKeyboard($formContents=array())
		{
			$is_user_keyboard = false;
			if (empty($formContents[self::nonce_keyboard_press])) {
				$formContents[self::nonce_keyboard_press] = isset($_REQUEST[self::nonce_keyboard_press]) ? $_REQUEST[self::nonce_keyboard_press] : '';
			}
			if (isset($formContents[self::nonce_keyboard_press])) {
				if (is_numeric($formContents[self::nonce_keyboard_press]) !== false) {
					$is_user_keyboard = true;
				}
			}
			return $is_user_keyboard;
		}
		function verifyNonceField($nonce_value='')
		{
			$return = '';
			if (empty($nonce_value)) {
				$nonce_value = isset($_REQUEST[self::nonce_name]) ? $_REQUEST[self::nonce_name] : '';
			}
			if (function_exists('wp_verify_nonce')) {
				$nonce = wp_verify_nonce($nonce_value, self::nonce_action);
				switch ($nonce) {
					case 1:
						$return = __('Nonce is less than 12 hours old', 'ARMember');
						break;

					case 2:
						$return = __('Nonce is between 12 and 24 hours old', 'ARMember');
						break;

					default:
						$return = false;
				}
			}
			return $return;
		}
		function validateReferer()
		{
			if (isset($_SERVER['HTTPS'])) {
				$protocol = "https://";
			} else {
				$protocol = "http://";
			}
			$absurl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
			$absurlParsed = parse_url($absurl);
			$result["pass"] = false;
			$result["hasReferrer"] = false;
			$httpReferer = $_SERVER['HTTP_REFERER'];
			if (isset($httpReferer)) {
				$refererParsed = parse_url($httpReferer);
				if (isset($refererParsed['host'])) {
					$result["hasReferrer"] = true;
					$absUrlRegex = '/' . strtolower($absurlParsed['host']) . '/';
					$isRefererValid = preg_match($absUrlRegex, strtolower($refererParsed['host']));
					if ($isRefererValid == 1) {
						$result["pass"] = true;
					}
				} else {
					$result["status"] = "Absolute URL: " . $absurl . " Referer: " . $httpReferer;
				}
			} else {
				$result["status"] = "Absolute URL: " . $absurl . " Referer: " . $httpReferer;
			}
			return $result;
		}
		function test_form()
		{
			global $wpdb;
			if( isset($_POST) && !empty($_POST) )
			{
				$validate = apply_filters('armember_validate_spam_filter_fields', true);
				if($validate)
				{
					$data = maybe_serialize($_POST);
				} else {
					$data = 'Spam Submit';
				}
				var_dump($data);
			}
			?>
			<form method="POST">
				<table>
					<tr>
						<td>Name</td>
						<td><input type="text" name="test_name" value=""></td>
					</tr>
					<tr>
						<td>Email</td>
						<td><input type="email" name="test_email" value=""></td>
					</tr>
					<tr>
						<td>Gender</td>
						<td>
							<input type="radio" class="iradio" name="test_gender" value="male"> Male<br/>
							<input type="radio" class="iradio" name="test_gender" value="female"> Female
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
				<?php echo do_shortcode('[armember_spam_filters]');?>
			</form>
			<?php
		}
	}
}
global $Spam_Filter;
$Spam_Filter = new Spam_Filter();