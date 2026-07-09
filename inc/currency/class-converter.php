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
		'ANG' => 'Cg',
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

	/** @var array<string, array<string, string>>|null */
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
			$labels[ $currency ] = trim( $symbol . ' ' . $code );
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

		return round( $amount * $rate, 2 );
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
				? $raw_price
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
		$formatted = number_format( $amount, 2, ',', '.' );
		$formatted = str_replace( ',00', ',-', $formatted );

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
	 * @return array<string, array<string, string>>
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
	 * @param array<string, array<string, string>> $prices All property prices.
	 * @return void
	 */
	public static function save_all_property_prices( array $prices ): void {
		self::$property_prices_cache = $prices;

		update_option( self::OPTION_PROPERTY_PRICES, $prices, false );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, string>
	 */
	public static function get_formatted_prices( int $post_id ): array {
		$all = self::get_all_property_prices();

		return self::normalize_prices( $all[ (string) $post_id ] ?? [] );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, string>
	 */
	public static function get_display_prices( int $post_id ): array {
		$prices = self::get_formatted_prices( $post_id );

		if ( '' !== ( $prices[ self::DEFAULT_DISPLAY_CURRENCY ] ?? '' ) ) {
			return $prices;
		}

		self::sync_formatted_prices( $post_id );

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
	 * Compute and persist formatted prices for one property.
	 *
	 * @param int $post_id Property post ID.
	 * @return void
	 */
	public static function sync_formatted_prices( int $post_id ): void {
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

		$formatted = self::compute_formatted_prices( $post_id );

		if ( empty( $formatted ) ) {
			unset( $all[ $key ] );
			self::save_all_property_prices( $all );

			return;
		}

		$all[ $key ] = $formatted;
		self::save_all_property_prices( $all );
	}

	/**
	 * Refresh formatted prices for all published properties (cron).
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

			$formatted = self::compute_formatted_prices( $post_id );

			if ( ! empty( $formatted ) ) {
				$all[ (string) $post_id ] = $formatted;
			}
		}

		self::save_all_property_prices( $all );
		update_option( 'sunset_property_prices_synced', (string) time(), false );
	}

	/**
	 * @param int $post_id Property post ID.
	 * @return array<string, string>
	 */
	private static function compute_formatted_prices( int $post_id ): array {
		$data = self::get_property_prices( $post_id );

		if ( empty( $data['prices'] ) ) {
			return [];
		}

		$formatted = [];

		foreach ( $data['prices'] as $currency => $price_data ) {
			$formatted[ $currency ] = (string) ( $price_data['formatted'] ?? '' );
		}

		foreach ( [ 'EUR', 'USD', 'ANG' ] as $currency ) {
			if ( '' === ( $formatted[ $currency ] ?? '' ) ) {
				return [];
			}
		}

		return $formatted;
	}

	/**
	 * @param mixed $prices Stored prices.
	 * @return array<string, string>
	 */
	private static function normalize_prices( $prices ): array {
		if ( ! is_array( $prices ) ) {
			return [];
		}

		$normalized = [];

		foreach ( [ 'EUR', 'USD', 'ANG' ] as $currency ) {
			$normalized[ $currency ] = isset( $prices[ $currency ] ) ? (string) $prices[ $currency ] : '';
		}

		return $normalized;
	}
}
