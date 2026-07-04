<?php
/**
 * Shortcodes - Currency switcher
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Shortcodes\Currency_Switcher;

use TAB\Sunset_Realtors\Currency\Converter;

add_shortcode( 'sunset_currency_switcher', __NAMESPACE__ . '\\sunset_currency_switcher_shortcode' );

/**
 * Dropdown to switch displayed property prices site-wide.
 *
 * @param array<string, string> $args Shortcode attributes.
 * @return string
 */
function sunset_currency_switcher_shortcode( $args = [] ): string {
	$options = shortcode_atts(
		[
			'class' => '',
			'label' => __( 'Currency', SUNSET_REALTORS_PLUGIN_DOMAIN ),
		],
		$args,
		'sunset_currency_switcher'
	);

	$selected   = Converter::get_selected_currency();
	$class      = trim( 'sunset-currency-switcher ' . $options['class'] );
	$select_id  = wp_unique_id( 'sunset-currency-switcher-' );
	$currencies = Converter::get_switcher_currencies();

	$options_html = implode(
		'',
		array_map(
			static function ( $code, $label ) use ( $selected, $select_id ) {
				return sprintf(
					'<li class="sunset-currency-switcher__option%1$s" id="%2$s-option-%3$s" role="option" data-value="%3$s" tabindex="-1" aria-selected="%4$s">%5$s</li>',
					( $selected === $code ? ' is-selected' : '' ),
					esc_attr( $select_id ),
					esc_attr( $code ),
					( $selected === $code ? 'true' : 'false' ),
					esc_html( $label )
				);
			},
			array_keys( $currencies ),
			$currencies
		)
	);

	return sprintf(
		'<div class="%1$s sunset-currency-switcher--custom" data-selected-currency="%4$s">
			<span class="screen-reader-text">%2$s</span>
			<button type="button" class="sunset-currency-switcher__toggle" aria-haspopup="listbox" aria-expanded="false" aria-controls="%5$s">
				<span class="sunset-currency-switcher__toggle-label">%6$s</span>
				<span class="sunset-currency-switcher__toggle-icon" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
				</span>
			</button>
			<ul class="sunset-currency-switcher__list" role="listbox" id="%5$s" aria-label="%2$s" aria-activedescendant="%5$s-option-%4$s">
				%3$s
			</ul>
		</div>',
		esc_attr( $class ),
		esc_html( $options['label'] ),
		$options_html,
		esc_attr( $selected ),
		esc_attr( $select_id ),
		esc_html( $currencies[ $selected ] ?? $selected )
	);
}
