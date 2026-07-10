/**
 * Site-wide currency switcher (cookie + DOM update).
 */

/**
 * Internal dependencies
 */
import {
	COOKIE_CURRENCY,
	SUPPORTED_CURRENCIES,
	SWITCHER_SELECTOR,
} from './currency-switcher-config.js';
import {
	closeAllSwitchers,
	initSwitcherToggle,
	updateSwitcherUI,
} from './currency-switcher-toggle.js';
import { formatPrice } from './currency-format.js';

const PRICE_SELECTOR = '.sunset-property-price[data-price-eur]';

const PRICE_ATTRIBUTES = {
	EUR: 'data-price-eur',
	USD: 'data-price-usd',
	ANG: 'data-price-ang',
};

const COOKIE_MAX_AGE = 60 * 60 * 24 * 365;

/**
 * @param {string} name Cookie name.
 * @return {string} Cookie value.
 */
const getCookie = (name) => {
	const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

	return match ? decodeURIComponent(match[1]) : '';
};

/**
 * @param {string} name  Cookie name.
 * @param {string} value Cookie value.
 * @return {void}
 */
const setCookie = (name, value) => {
	const secure = window.location.protocol === 'https:' ? '; Secure' : '';

	document.cookie = `${name}=${encodeURIComponent(value)}; Path=/; Max-Age=${COOKIE_MAX_AGE}; SameSite=Lax${secure}`;
};

/**
 * @return {string} Selected currency.
 */
const getSelectedCurrency = () => {
	const fromCookie = getCookie(COOKIE_CURRENCY);

	return SUPPORTED_CURRENCIES.includes(fromCookie) ? fromCookie : 'EUR';
};

/**
 * @param {string} currency Currency code.
 * @return {void}
 */
const applyCurrency = (currency) => {
	const attribute = PRICE_ATTRIBUTES[currency];

	if (!attribute) {
		return;
	}

	document.querySelectorAll(PRICE_SELECTOR).forEach((element) => {
		const rawAmount = element.getAttribute(attribute);
		const price = formatPrice(rawAmount, currency);

		if (!price) {
			return;
		}

		const amount =
			element.querySelector('.sunset-property-price__amount') ?? element;

		amount.textContent = price;
		element.setAttribute('data-currency', currency);
	});

	document.querySelectorAll(SWITCHER_SELECTOR).forEach((switcher) => {
		updateSwitcherUI(switcher, currency);
	});
};

/**
 * @param {string} currency Currency code.
 * @return {void}
 */
const selectCurrency = (currency) => {
	if (!SUPPORTED_CURRENCIES.includes(currency)) {
		return;
	}

	setCookie(COOKIE_CURRENCY, currency);
	applyCurrency(currency);
	closeAllSwitchers();
};

/**
 * @return {void}
 */
const initCurrencySwitcher = () => {
	const switchers = document.querySelectorAll(SWITCHER_SELECTOR);
	const hasPrices = document.querySelector(PRICE_SELECTOR);

	if (!switchers.length && !hasPrices) {
		return;
	}

	applyCurrency(getSelectedCurrency());
	initSwitcherToggle(switchers, selectCurrency);
};

export default initCurrencySwitcher;
