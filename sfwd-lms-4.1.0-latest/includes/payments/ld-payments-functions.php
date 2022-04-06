<?php
/**
 * Functions related to payments
 *
 * @since 4.1.0
 *
 * @package LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs the LearnDash global currency symbol.
 *
 * @since 4.1.0
 */
function learndash_the_currency_symbol() {
	echo wp_kses_post( learndash_get_currency_symbol() );
}

/**
 * Gets the LearnDash global currency symbol.
 *
 * @since 4.1.0
 *
 * @return string Returns currency symbol.
 */
function learndash_get_currency_symbol() {
	$currency = learndash_get_currency_code();

	if ( class_exists( 'NumberFormatter' ) ) {
		$locale        = get_locale();
		$number_format = new NumberFormatter( $locale . '@currency=' . $currency, NumberFormatter::CURRENCY );
		$currency      = $number_format->getSymbol( NumberFormatter::CURRENCY_SYMBOL );
	}

	/**
	 * Filter the LearnDash global currency symbol.
	 *
	 * @since 4.1.0
	 *
	 * @param string $currency The currency symbol.
	 */
	return apply_filters( 'learndash_currency_symbol', $currency );
}

/**
 * Gets the LearnDash global currency code.
 *
 * @since 4.1.0
 *
 * @return string Returns currency code.
 */
function learndash_get_currency_code() {
	$currency = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_Payments_Defaults', 'currency' );
	/**
	 * Filter the LearnDash global currency code.
	 *
	 * @since 4.1.0
	 *
	 * @param string $currency The currency code.
	 */
	return apply_filters( 'learndash_currency_code', $currency );
}

/**
 * Gets the price formatted based on the LearnDash global currency configuration.
 *
 * @since 4.1.0
 *
 * @param   float $price The price to format.
 *
 * @return string Returns price formatted.
 */
function learndash_get_price_formatted( $price ) {
	$currency = learndash_get_currency_code();

	if ( class_exists( 'NumberFormatter' ) ) {
		$locale          = get_locale();
		$number_format   = new NumberFormatter( $locale . '@currency=' . $currency, NumberFormatter::CURRENCY );
		$price_formatted = $number_format->format( $price );
	} else {
		$price_formatted = "$price $currency";
	}

	return $price_formatted;
}

/**
 * Checks currency code is a zero decimal currency.
 *
 * @param string $currency Stripe currency ISO code.
 *
 * @return bool
 */
function learndash_is_zero_decimal_currency( string $currency = '' ): bool {
	$zero_decimal_currencies = array(
		'BIF',
		'CLP',
		'DJF',
		'GNF',
		'JPY',
		'KMF',
		'KRW',
		'MGA',
		'PYG',
		'RWF',
		'VND',
		'VUV',
		'XAF',
		'XOF',
		'XPF',
	);

	return in_array( strtoupper( $currency ), $zero_decimal_currencies, true );
}
