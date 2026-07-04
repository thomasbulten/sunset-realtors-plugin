/**
 * Currency switcher dropdown toggle behaviour.
 */

/**
 * Internal dependencies
 */
import { SWITCHER_SELECTOR } from './currency-switcher-config.js';

/**
 * @param {HTMLElement} switcher Switcher root element.
 * @return {void}
 */
export const closeSwitcher = (switcher) => {
	const toggle = switcher.querySelector('.sunset-currency-switcher__toggle');
	const list = switcher.querySelector('.sunset-currency-switcher__list');

	if (!toggle || !list) {
		return;
	}

	list.classList.remove('is-open');
	toggle.setAttribute('aria-expanded', 'false');
};

/**
 * @return {void}
 */
export const closeAllSwitchers = () => {
	document.querySelectorAll(SWITCHER_SELECTOR).forEach(closeSwitcher);
};

/**
 * @param {HTMLElement} switcher Switcher root element.
 * @return {void}
 */
export const openSwitcher = (switcher) => {
	closeAllSwitchers();

	const toggle = switcher.querySelector('.sunset-currency-switcher__toggle');
	const list = switcher.querySelector('.sunset-currency-switcher__list');

	if (!toggle || !list) {
		return;
	}

	list.classList.add('is-open');
	toggle.setAttribute('aria-expanded', 'true');
};

/**
 * @param {HTMLElement} switcher Switcher root element.
 * @return {void}
 */
export const toggleSwitcher = (switcher) => {
	const list = switcher.querySelector('.sunset-currency-switcher__list');

	if (list?.classList.contains('is-open')) {
		closeSwitcher(switcher);
		return;
	}

	openSwitcher(switcher);
};

/**
 * @param {HTMLElement} switcher Switcher root element.
 * @param {string}      currency Currency code.
 * @return {void}
 */
export const updateSwitcherUI = (switcher, currency) => {
	switcher.dataset.selectedCurrency = currency;

	const selectedOption = switcher.querySelector(
		`.sunset-currency-switcher__option[data-value="${currency}"]`,
	);
	const toggleLabel = switcher.querySelector(
		'.sunset-currency-switcher__toggle-label',
	);
	const list = switcher.querySelector('.sunset-currency-switcher__list');

	if (toggleLabel && selectedOption) {
		toggleLabel.textContent = selectedOption.textContent.trim();
	}

	switcher
		.querySelectorAll('.sunset-currency-switcher__option')
		.forEach((option) => {
			const isSelected = option.dataset.value === currency;

			option.classList.toggle('is-selected', isSelected);
			option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
		});

	if (list && selectedOption) {
		list.setAttribute('aria-activedescendant', selectedOption.id);
	}
};

/**
 * @param {HTMLElement[]}              switchers Switcher root elements.
 * @param {(currency: string) => void} onSelect  Currency select callback.
 * @return {void}
 */
export const initSwitcherToggle = (switchers, onSelect) => {
	switchers.forEach((switcher) => {
		const toggle = switcher.querySelector(
			'.sunset-currency-switcher__toggle',
		);

		toggle?.addEventListener('click', (event) => {
			event.stopPropagation();
			toggleSwitcher(switcher);
		});

		switcher
			.querySelectorAll('.sunset-currency-switcher__option')
			.forEach((option) => {
				option.addEventListener('click', (event) => {
					event.stopPropagation();

					const value = option.dataset.value ?? '';

					if (!value) {
						return;
					}

					onSelect(value);
				});
			});
	});

	document.addEventListener('click', closeAllSwitchers);

	document.addEventListener('keydown', (event) => {
		if ('Escape' === event.key) {
			closeAllSwitchers();
		}
	});
};
