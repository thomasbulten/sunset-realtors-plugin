<?php

/**
 * Exchange rates service (ExchangeRate-API + cache).
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Currency;

final class Rates_Service
{
    public const TRANSIENT_KEY = 'sunset_currency_rates';

    public const OPTION_API_KEY = 'sunset_exchangerate_api_key';

    public const OPTION_NEXT_UPDATE = 'sunset_rates_next_update';

    /** Buffer after API publish time before syncing prices. */
    public const SYNC_BUFFER_SECONDS = 15 * MINUTE_IN_SECONDS;

    /**
     * @return string
     */
    public static function get_api_key(): string
    {
        if (defined('SUNSET_EXCHANGERATE_API_KEY') && '' !== SUNSET_EXCHANGERATE_API_KEY) {
            return (string) SUNSET_EXCHANGERATE_API_KEY;
        }

        return (string) get_option(self::OPTION_API_KEY, '');
    }

    /**
     * @return array<string, float>
     */
    public static function get_rates(): array
    {
        $cached = get_transient(self::TRANSIENT_KEY);

        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        return [];
    }

    /**
     * Ensure rates are available for background sync (cron / save).
     *
     * @return array<string, float>
     */
    public static function ensure_rates(): array
    {
        $rates = self::get_rates();

        if (! empty($rates)) {
            return $rates;
        }

        return self::refresh_rates();
    }

    /**
     * @return array<string, float>
     */
    public static function refresh_rates(): array
    {
        $rates = self::fetch_from_api();

        if (! empty($rates)) {
            set_transient(self::TRANSIENT_KEY, $rates, DAY_IN_SECONDS);
        }

        return $rates;
    }

    /**
     * Unix timestamp of the next ExchangeRate-API data refresh.
     *
     * @return int
     */
    public static function get_next_update_timestamp(): int
    {
        return (int) get_option(self::OPTION_NEXT_UPDATE, 0);
    }

    /**
     * Preferred timestamp for the next property price sync cron.
     *
     * @return int
     */
    public static function get_next_sync_timestamp(): int
    {
        $next_update = self::get_next_update_timestamp();

        if ($next_update > time()) {
            return $next_update + self::SYNC_BUFFER_SECONDS;
        }

        return time() + DAY_IN_SECONDS;
    }

    /**
     * @return array<string, float>
     */
    private static function fetch_from_api(): array
    {
        $api_key = self::get_api_key();

        if ('' === $api_key) {
            return [];
        }

        $url      = sprintf(
            'https://v6.exchangerate-api.com/v6/%s/latest/EUR',
            rawurlencode($api_key)
        );
        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response)) {
            return [];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (
            ! is_array($body)
            || 'success' !== ($body['result'] ?? '')
            || empty($body['conversion_rates'])
            || ! is_array($body['conversion_rates'])
        ) {
            return [];
        }

        $eur_usd = (float) ($body['conversion_rates']['USD'] ?? 0);
        $eur_ang = (float) ($body['conversion_rates']['ANG'] ?? 0);

        if ($eur_usd <= 0 || $eur_ang <= 0) {
            return [];
        }

        $next_update = (int) ($body['time_next_update_unix'] ?? 0);

        if ($next_update > 0) {
            update_option(self::OPTION_NEXT_UPDATE, $next_update, false);
        }

        $usd_eur = 1 / $eur_usd;
        $ang_eur = 1 / $eur_ang;
        $ang_usd = $ang_eur * $eur_usd;
        $usd_ang = 1 / $ang_usd;

        return [
            'EUR_USD' => $eur_usd,
            'EUR_ANG' => $eur_ang,
            'USD_EUR' => $usd_eur,
            'USD_ANG' => $usd_ang,
            'ANG_EUR' => $ang_eur,
            'ANG_USD' => $ang_usd,
        ];
    }

    /**
     * @param string $from Source currency.
     * @param string $to   Target currency.
     * @return float
     */
    public static function get_rate(string $from, string $to): float
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from === $to) {
            return 1.0;
        }

        $rates = self::get_rates();
        $key   = $from . '_' . $to;

        if (isset($rates[$key]) && (float) $rates[$key] > 0) {
            return (float) $rates[$key];
        }

        $inverse_key = $to . '_' . $from;

        if (isset($rates[$inverse_key]) && (float) $rates[$inverse_key] > 0) {
            return 1 / (float) $rates[$inverse_key];
        }

        return 0.0;
    }
}
