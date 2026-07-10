/**
 * Shared currency price formatting (matches PHP Converter::format).
 */

/**
 * Internal dependencies
 */
import { CURRENCY_SYMBOLS } from './currency-switcher-config.js';

/**
 * @param {number|string} amount   Numeric amount.
 * @param {string}        currency Currency code.
 * @return {string} Formatted price or empty string.
 */
export const formatPrice = (amount, currency) => {
	const value = Number(amount);

	if (!Number.isFinite(value) || value <= 0) {
		return '';
	}

	const symbol = CURRENCY_SYMBOLS[currency] ?? currency;
	const formatted =
		Math.round(value).toLocaleString('nl-NL', {
			maximumFractionDigits: 0,
		}) + ',-';

	return `${symbol} ${formatted}`;
};
