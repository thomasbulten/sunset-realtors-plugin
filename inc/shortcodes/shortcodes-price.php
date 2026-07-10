<?php
/**
 * Shortcodes - Price
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Shortcodes\Price;

use TAB\Sunset_Realtors\Currency\Converter;
use function TAB\Sunset_Realtors\Helpers\General\get_names;

add_shortcode( 'sunset_price', __NAMESPACE__ . '\\sunset_price_shortcode' );

/**
 * Shortcode to display the price of a property.
 *
 * @param array<string, string> $args The shortcode arguments.
 * @return string The price of the property.
 */
function sunset_price_shortcode( $args = [] ): string {
	$options = shortcode_atts(
		[
			'id'    => '',
			'type'  => '',
			'class' => 'c-property__price sunset-property-price',
		],
		$args,
		'sunset_price'
	);

	$post_id = ! empty( $options['id'] ) ? absint( $options['id'] )
		: ('single' === $options['type'] ? get_queried_object_id() : get_the_ID());

	if ( ! $post_id ) {
		return '';
	}

	$class_names = get_names([
		$options['class'],
	]);

	if ( 'prijs_op_aanvraag' === get_post_meta( $post_id, 'price_type', true ) ) {
		return sprintf(
			'<span class="%1$s">%2$s</span>',
			esc_attr( $class_names ),
			Converter::get_price_on_request_text()
		);
	}

	$amounts          = Converter::get_display_amounts( $post_id );
	$display_currency = Converter::get_selected_currency();
	$display_amount   = $amounts[ $display_currency ] ?? 0.0;
	$display          = Converter::format( $display_amount, $display_currency );

	if ( $display_amount <= 0 ) {
		return sprintf(
			'<span class="%1$s">%2$s</span>',
			esc_attr( $class_names ),
			Converter::get_price_on_request_text()
		);
	}

	$attributes = sprintf(
		' data-currency="%1$s" data-price-eur="%2$s" data-price-usd="%3$s" data-price-ang="%4$s"',
		esc_attr( $display_currency ),
		esc_attr( (string) ( $amounts['EUR'] ?? 0 ) ),
		esc_attr( (string) ( $amounts['USD'] ?? 0 ) ),
		esc_attr( (string) ( $amounts['ANG'] ?? 0 ) )
	);

	$sale_condition = Converter::get_sale_condition( $post_id );
	$condition_html = '' !== $sale_condition
		? sprintf(
			'<span class="sunset-property-price__condition"> %s</span>',
			$sale_condition
		)
		: '';

	return sprintf(
		'<span class="%s"%s><span class="sunset-property-price__amount">%s</span>%s</span>',
		esc_attr( $class_names ),
		$attributes,
		esc_html( $display ),
		$condition_html
	);
}
