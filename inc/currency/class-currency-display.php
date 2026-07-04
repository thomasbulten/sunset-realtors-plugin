<?php

/**
 * Currency display hooks and price sync scheduling.
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Currency;

use TAB\Sunset_Realtors\Listing\Listing_Meta;

final class Currency_Display {

	public const CRON_HOOK = 'sunset_sync_property_prices';

	/**
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'ysd_mapi_formatted_locale_price', [ self::class, 'filter_formatted_price' ], 10, 2 );
		add_action( 'save_post_' . Listing_Meta::POST_TYPE, [ self::class, 'sync_property_prices' ], 99, 2 );
		add_action( 'before_delete_post', [ self::class, 'remove_property_prices' ] );
		add_action( self::CRON_HOOK, [ self::class, 'run_scheduled_sync' ] );
		add_action( 'init', [ self::class, 'schedule_crons' ], 20 );
	}

	/**
	 * Ensure the next price sync is scheduled.
	 *
	 * @return void
	 */
	public static function schedule_crons(): void {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			self::schedule_next_sync();
		}
	}

	/**
	 * Schedule the next price sync shortly after ExchangeRate-API publishes new data.
	 *
	 * @return void
	 */
	public static function schedule_next_sync(): void {
		if ( '' !== Rates_Service::get_api_key() ) {
			$next_update = Rates_Service::get_next_update_timestamp();

			if ( 0 === $next_update || $next_update <= time() ) {
				Rates_Service::refresh_rates();
			}
		}

		wp_clear_scheduled_hook( self::CRON_HOOK );

		$timestamp = max( Rates_Service::get_next_sync_timestamp(), time() + MINUTE_IN_SECONDS );

		if ( ! wp_schedule_single_event( $timestamp, self::CRON_HOOK ) ) {
			wp_schedule_single_event( time() + HOUR_IN_SECONDS, self::CRON_HOOK );
		}
	}

	/**
	 * Cron callback: refresh rates, rebuild prices, schedule the next run.
	 *
	 * @return void
	 */
	public static function run_scheduled_sync(): void {
		Rates_Service::refresh_rates();
		Converter::sync_all_properties();
		self::schedule_next_sync();
	}

	/**
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public static function sync_property_prices( int $post_id, \WP_Post $post ): void {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		Converter::sync_formatted_prices( $post_id );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function remove_property_prices( int $post_id ): void {
		if ( Listing_Meta::POST_TYPE !== get_post_type( $post_id ) ) {
			return;
		}

		Converter::remove_property_prices( $post_id );
	}

	/**
	 * @param string   $price    Formatted price.
	 * @param \WP_Post $property Property post.
	 * @return string
	 */
	public static function filter_formatted_price( string $price, \WP_Post $property ): string {
		$stored   = Converter::get_display_prices( $property->ID );
		$currency = Converter::get_selected_currency();
		$display  = $stored[ $currency ] ?? '';

		if ( '' !== $display ) {
			return $display;
		}

		return $price;
	}
}
