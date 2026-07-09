<?php
/**
 * Shortcodes - Price
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Shortcodes\Price;

use TAB\Sunset_Realtors\Currency\Converter;

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
			'tag'   => 'span',
			'class' => 'c-property__price sunset-property-price',
		],
		$args,
		'sunset_price'
	);


	$post_id = ! empty( $options['id'] ) ? absint( $options['id'] )
		: (is_singular('property') ? get_queried_object_id() : get_the_ID());

	if ( ! $post_id ) {
		return '';
	}

	$class_names = trim( $options['class'] );

	if ( 'prijs_op_aanvraag' === get_post_meta( $post_id, 'price_type', true ) ) {
		return sprintf(
			'<%1$s class="%2$s">%3$s</%1$s>',
			tag_escape( $options['tag'] ),
			esc_attr( $class_names ),
			esc_html( Converter::get_price_on_request_text() )
		);
	}

	$prices = Converter::get_display_prices( $post_id );

	$display_currency = Converter::get_selected_currency();
	$display          = $prices[ $display_currency ] ?? '';

	if ( '' === $display ) {
		return sprintf(
			'<%1$s class="%2$s">%3$s</%1$s>',
			tag_escape( $options['tag'] ),
			esc_attr( $class_names ),
			esc_html( Converter::get_price_on_request_text() )
		);
	}

	$attributes = sprintf(
		' data-currency="%1$s" data-price-eur="%2$s" data-price-usd="%3$s" data-price-ang="%4$s"',
		esc_attr( $display_currency ),
		esc_attr( $prices['EUR'] ?? '' ),
		esc_attr( $prices['USD'] ?? '' ),
		esc_attr( $prices['ANG'] ?? '' )
	);

	$sale_condition = Converter::get_sale_condition( $post_id );
	$condition_html = '' !== $sale_condition
		? sprintf(
			'<span class="sunset-property-price__condition"> %s</span>',
			esc_html( $sale_condition )
		)
		: '';

	return sprintf(
		'<%1$s class="%2$s"%3$s><span class="sunset-property-price__amount">%4$s</span>%5$s</%1$s>',
		tag_escape( $options['tag'] ),
		esc_attr( $class_names ),
		$attributes,
		esc_html( $display ),
		$condition_html
	);
}
