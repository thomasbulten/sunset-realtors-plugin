<?php

/**
 * Currency conversion helpers.
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Currency;

use TAB\Sunset_Realtors\Listing\Listing_Meta;

final class Converter {

	public const OPTION_PROPERTY_PRICES = 'sunset_property_prices';

	/** @var array<string, string> */
	public const SYMBOLS = [
		'EUR' => '€',
		'USD' => '$',
		'ANG' => 'XCG',
	];

	/**
	 * Currency codes as they should be shown as text. Internally we keep 'ANG',
	 * but the Caribbean guilder's ISO code is 'XCG'.
	 *
	 * @var array<string, string>
	 */
	public const DISPLAY_CODES = [
		'ANG' => 'XCG',
	];

	public const DEFAULT_DISPLAY_CURRENCY = 'EUR';

	public const COOKIE_CURRENCY = 'sunset_currency';

	/** @var array<string> */
	public const SUPPORTED_CURRENCIES = [ 'EUR', 'USD', 'ANG' ];

	/** @var array<string, array<string, float|int|string>>|null */
	private static ?array $property_prices_cache = null;

	/**
	 * @return string
	 */
	public static function get_selected_currency(): string {
		$raw = filter_input( INPUT_COOKIE, self::COOKIE_CURRENCY );

		if ( ! is_string( $raw ) || '' === $raw ) {
			return self::DEFAULT_DISPLAY_CURRENCY;
		}

		$cookie = sanitize_text_field( wp_unslash( $raw ) );

		return in_array( $cookie, self::SUPPORTED_CURRENCIES, true ) ? $cookie : self::DEFAULT_DISPLAY_CURRENCY;
	}

	/**
	 * @return array<string, string>
	 */
	public static function get_switcher_currencies(): array {
		$labels = [];

		foreach ( self::SUPPORTED_CURRENCIES as $currency ) {
			$symbol            = self::SYMBOLS[ $currency ] ?? $currency;
			$code              = self::DISPLAY_CODES[ $currency ] ?? $currency;
			$labels[ $currency ] = $symbol === $code ? $symbol : trim( $symbol . ' ' . $code );
		}

		return $labels;
	}

	/**
	 * @param float  $amount Amount to convert.
	 * @param string $from   Source currency.
	 * @param string $to     Target currency.
	 * @return float
	 */
	public static function convert( float $amount, string $from, string $to ): float {
		$rate = Rates_Service::get_rate( $from, $to );

		if ( $rate <= 0 ) {
			return 0.0;
		}

		return round( $amount * $rate, 0 );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, mixed>
	 */
	public static function get_property_prices( int $post_id ): array {
		$property = get_post( $post_id );

		if ( ! $property instanceof \WP_Post ) {
			return [];
		}

		$raw_price  = self::parse_price( (string) get_post_meta( $post_id, 'price', true ) );
		$base       = strtoupper( (string) ( get_post_meta( $post_id, Listing_Meta::META_PRICE_CURRENCY, true ) ?: 'EUR' ) );
		$currencies = [ 'EUR', 'USD', 'ANG' ];
		$prices     = [];

		foreach ( $currencies as $currency ) {
			$amount = $currency === $base
				? round( $raw_price, 0 )
				: self::convert( $raw_price, $base, $currency );

			$prices[ $currency ] = [
				'amount'    => $amount,
				'formatted' => self::format( $amount, $currency ),
				'is_base'   => $currency === $base,
			];
		}

		return [
			'property_id' => $post_id,
			'base'        => $base,
			'prices'      => $prices,
		];
	}

	/**
	 * @param string $price Raw price string.
	 * @return float
	 */
	public static function parse_price( string $price ): float {
		$normalized = str_replace( [ '.', ',' ], [ '', '' ], $price );

		return (float) $normalized;
	}

	/**
	 * @param float  $amount   Amount.
	 * @param string $currency Currency code.
	 * @return string
	 */
	public static function format( float $amount, string $currency ): string {
		if ( $amount <= 0 ) {
			return '';
		}

		$symbol    = self::SYMBOLS[ $currency ] ?? $currency;
		$formatted = number_format( $amount, 0, ',', '.' ) . ',-';

		return trim( $symbol . ' ' . $formatted );
	}

	/**
	 * @return string
	 */
	public static function get_price_on_request_text(): string {
		if ( defined( 'YSD_MAPI_TEXT_DOMAIN' ) ) {
			return (string) __( 'Price on request', YSD_MAPI_TEXT_DOMAIN );
		}

		return (string) __( 'Price on request', SUNSET_REALTORS_PLUGIN_DOMAIN );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return string
	 */
	public static function get_sale_condition( int $post_id ): string {
		$property = get_post( $post_id );

		if ( ! $property instanceof \WP_Post ) {
			return '';
		}

		if ( ! function_exists( 'getPropertySaleCondition' ) ) {
			return '';
		}

		return trim( (string) getPropertySaleCondition( $property ) );
	}

	/**
	 * @return array<string, array<string, float|int|string>>
	 */
	public static function get_all_property_prices(): array {
		if ( null !== self::$property_prices_cache ) {
			return self::$property_prices_cache;
		}

		$stored = get_option( self::OPTION_PROPERTY_PRICES, [] );

		self::$property_prices_cache = is_array( $stored ) ? $stored : [];

		return self::$property_prices_cache;
	}

	/**
	 * @param array<string, array<string, float|int|string>> $prices All property prices.
	 * @return void
	 */
	public static function save_all_property_prices( array $prices ): void {
		self::$property_prices_cache = $prices;

		update_option( self::OPTION_PROPERTY_PRICES, $prices, false );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, float>
	 */
	public static function get_property_amounts( int $post_id ): array {
		$all = self::get_all_property_prices();

		return self::normalize_amounts( $all[ (string) $post_id ] ?? [] );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, float>
	 */
	public static function get_display_amounts( int $post_id ): array {
		$amounts = self::get_property_amounts( $post_id );

		if ( ( $amounts[ self::DEFAULT_DISPLAY_CURRENCY ] ?? 0.0 ) > 0 ) {
			return $amounts;
		}

		self::sync_property_prices( $post_id );

		return self::get_property_amounts( $post_id );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, string>
	 */
	public static function get_formatted_prices( int $post_id ): array {
		$amounts   = self::get_display_amounts( $post_id );
		$formatted = [];

		foreach ( self::SUPPORTED_CURRENCIES as $currency ) {
			$amount = $amounts[ $currency ] ?? 0.0;
			$formatted[ $currency ] = $amount > 0 ? self::format( $amount, $currency ) : '';
		}

		return $formatted;
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, string>
	 */
	public static function get_display_prices( int $post_id ): array {
		return self::get_formatted_prices( $post_id );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return void
	 */
	public static function remove_property_prices( int $post_id ): void {
		$all = self::get_all_property_prices();
		$key = (string) $post_id;

		if ( ! isset( $all[ $key ] ) ) {
			return;
		}

		unset( $all[ $key ] );
		self::save_all_property_prices( $all );
	}

	/**
	 * Compute and persist converted amounts for one property.
	 *
	 * @param int $post_id Property post ID.
	 * @return void
	 */
	public static function sync_property_prices( int $post_id ): void {
		$all = self::get_all_property_prices();
		$key = (string) $post_id;

		if ( Listing_Meta::POST_TYPE !== get_post_type( $post_id ) ) {
			unset( $all[ $key ] );
			self::save_all_property_prices( $all );

			return;
		}

		if ( 'prijs_op_aanvraag' === get_post_meta( $post_id, 'price_type', true ) ) {
			unset( $all[ $key ] );
			self::save_all_property_prices( $all );

			return;
		}

		Rates_Service::ensure_rates();

		$amounts = self::compute_property_amounts( $post_id );

		if ( empty( $amounts ) ) {
			unset( $all[ $key ] );
			self::save_all_property_prices( $all );

			return;
		}

		$all[ $key ] = $amounts;
		self::save_all_property_prices( $all );
	}

	/**
	 * Refresh converted amounts for all published properties (cron).
	 *
	 * @return void
	 */
	public static function sync_all_properties(): void {
		Rates_Service::ensure_rates();

		$post_ids = get_posts(
			[
				'post_type'      => Listing_Meta::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]
		);

		$all = [];

		foreach ( $post_ids as $post_id ) {
			$post_id = (int) $post_id;

			if ( 'prijs_op_aanvraag' === get_post_meta( $post_id, 'price_type', true ) ) {
				continue;
			}

			$amounts = self::compute_property_amounts( $post_id );

			if ( ! empty( $amounts ) ) {
				$all[ (string) $post_id ] = $amounts;
			}
		}

		self::save_all_property_prices( $all );
		update_option( 'sunset_property_prices_synced', (string) time(), false );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, float>
	 */
	private static function compute_property_amounts( int $post_id ): array {
		$data = self::get_property_prices( $post_id );

		if ( empty( $data['prices'] ) ) {
			return [];
		}

		$amounts = [];

		foreach ( $data['prices'] as $currency => $price_data ) {
			$amount = (float) ( $price_data['amount'] ?? 0 );
			$amounts[ $currency ] = $amount;
		}

		foreach ( self::SUPPORTED_CURRENCIES as $currency ) {
			if ( ( $amounts[ $currency ] ?? 0.0 ) <= 0 ) {
				return [];
			}
		}

		return $amounts;
	}

	/**
	 * @param mixed $prices Stored prices.
	 * @return array<string, float>
	 */
	private static function normalize_amounts( $prices ): array {
		if ( ! is_array( $prices ) ) {
			return [];
		}

		$normalized = [];

		foreach ( self::SUPPORTED_CURRENCIES as $currency ) {
			$normalized[ $currency ] = self::parse_stored_amount( $prices[ $currency ] ?? 0 );
		}

		return $normalized;
	}

	/**
	 * @param mixed $value Stored amount or legacy formatted price.
	 * @return float
	 */
	private static function parse_stored_amount( $value ): float {
		if ( is_int( $value ) || is_float( $value ) ) {
			return (float) $value;
		}

		if ( is_string( $value ) && is_numeric( $value ) ) {
			return (float) $value;
		}

		if ( ! is_string( $value ) || '' === $value ) {
			return 0.0;
		}

		return self::parse_price( $value );
	}
}
