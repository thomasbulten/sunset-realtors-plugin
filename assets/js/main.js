/**
 * Main frontend entry point
 */

/**
 * Internal dependencies
 */
import '../css/style.scss';
import initCurrencySwitcher from './components/currency-switcher';

document.addEventListener('DOMContentLoaded', () => {
	initCurrencySwitcher();
});
