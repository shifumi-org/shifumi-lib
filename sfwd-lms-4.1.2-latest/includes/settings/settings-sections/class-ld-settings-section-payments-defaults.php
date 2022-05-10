<?php
/**
 * LearnDash Settings Section for Payments Defaults Configurations Metabox.
 *
 * @since 4.1.0
 * @package LearnDash\Settings\Sections
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Section' ) ) && ( ! class_exists( 'LearnDash_Settings_Section_Payments_Defaults' ) ) ) {

	/**
	 * Class LearnDash Settings Section for Payments Defaults Configurations Metabox.
	 *
	 * @since 4.1.0
	 */
	class LearnDash_Settings_Section_Payments_Defaults extends LearnDash_Settings_Section {

		/**
		 * Protected constructor for class
		 *
		 * @since 4.1.0
		 */
		protected function __construct() {
			$this->settings_page_id = 'learndash_lms_payments';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_settings_payments_defaults';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_settings_payments_defaults';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'settings_payments_defaults';

			// Section label/header.
			$this->settings_section_label = esc_html__( 'Default Payments Configurations', 'learndash' );

			parent::__construct();
		}

		/**
		 * Initialize the metabox settings values.
		 *
		 * @since 4.1.0
		 */
		public function load_settings_values() {
			parent::load_settings_values();

			// trying to get old currency data.
			if ( ! isset( $this->setting_option_values['currency'] ) || empty( $this->setting_option_values['currency'] ) ) {
				// Stripe add-on.
				$stripe_settings = get_option( 'learndash_stripe_settings' );
				if ( ! function_exists( 'is_plugin_active' ) ) {
					include_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				if ( is_plugin_active( 'learndash-stripe/learndash-stripe.php' ) && ! empty( $stripe_settings ) && ! empty( $stripe_settings['currency'] ) ) {
					$this->setting_option_values['currency'] = $stripe_settings['currency'];
				} else {
					// PayPal and Stripe Connect in LD core.
					$paypal_currency = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_PayPal', 'paypal_currency' );
					$stripe_currency = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Stripe_Connect', 'currency' );
					if ( 'on' === LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_PayPal', 'enabled' ) ) {
						$this->setting_option_values['currency'] = $paypal_currency;
					} else {
						$this->setting_option_values['currency'] = ! empty( $stripe_currency ) ? $stripe_currency : $paypal_currency;
					}
				}
			}
		}

			/**
			 * Validate Settings Currency field.
			 *
			 * @since 4.1.0
			 *
			 * @param string $val to be validated.
			 * @param string $key Settings key.
			 * @param array  $args Settings field args.
			 *
			 * @return string $val.
			 */
		public static function validate_currency( $val, $key, $args = array() ) {
				$val = sanitize_text_field( $val );

			if ( ! empty( $val ) && 3 !== strlen( $val ) ) {
				add_settings_error( $args['setting_option_key'], $key, esc_html__( 'Currency Code should be 3 letters.', 'learndash' ), 'error' );
			}

			return strtoupper( $val );
		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 4.1.0
		 */
		public function load_settings_fields() {
			$this->setting_option_fields = array();

			$this->setting_option_fields['currency'] = array(
				'name'              => 'currency',
				'type'              => 'text',
				'label'             => esc_html__( 'Currency', 'learndash' ),
				'help_text'         => sprintf(
					// translators: placeholder: Link to ISO 4217.
					esc_html_x( 'Enter the currency code for transactions. It should be one currency code from the %s list.', 'placeholder: URL to ISO 4217', 'learndash' ),
					'<a href="https://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank">' . esc_html__( 'ISO 4217', 'learndash' ) . '</a>'
				),
				'value'             => $this->setting_option_values['currency'] ?? '',
				'class'             => 'regular-text',
				'validate_callback' => array( $this, 'validate_currency' ),
			);

			/** This filter is documented in includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_section_key );

			parent::load_settings_fields();
		}
		// End of functions.
	}
}
add_action(
	'learndash_settings_sections_init',
	function() {
		LearnDash_Settings_Section_Payments_Defaults::add_section_instance();
	}
);
