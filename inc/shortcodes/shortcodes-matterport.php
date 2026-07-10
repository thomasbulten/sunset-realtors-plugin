<?php
/**
 * Shortcodes - Matterport
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Shortcodes\Matterport;

use TAB\Sunset_Realtors\Listing\Listing_Meta;
use function TAB\Sunset_Realtors\Helpers\General\get_names;

add_shortcode( 'sunset_matterport', __NAMESPACE__ . '\\sunset_matterport_shortcode' );
add_shortcode( 'sunset_matterport_link', __NAMESPACE__ . '\\sunset_matterport_link_shortcode' );

/**
 * Shortcode to display the matterport of a property.
 *
 * @param array<string, string> $args The shortcode arguments.
 * @return string The matterport of the property.
 */
function sunset_matterport_shortcode( $args = [] ): string {
	$options = shortcode_atts(
		[
			'class' => '',
		],
		$args,
		'sunset_matterport'
	);

	$matterport_id = get_matterport_id();

	if ( empty( $matterport_id ) ) {
		return '';
	}

	$class_names = get_names( [
		'c-property__matterport',
		'sunset-property-matterport',
		$options['class'],
	]);

	$content = sprintf(
		'<iframe width="853" height="480" src="https://my.matterport.com/show/?m=%s" frameborder="0" allowfullscreen allow="autoplay; fullscreen; web-share; xr-spatial-tracking;"></iframe>',
		esc_attr( $matterport_id ),
	);

	return sprintf(
		'<div id="matterport360" class="%s">%s</div>',
		esc_attr( $class_names ),
		$content,
	);
}

/**
 * Shortcode to display the link to the matterport of a property.
 *
 * @param array<string, string> $args The shortcode arguments.
 * @return string The link to the matterport of the property.
 */
function sunset_matterport_link_shortcode( $args = [] ): string {
	$options = shortcode_atts(
		[
			'class' => '',
		],
		$args,
		'sunset_matterport_link'
	);

	$matterport_id = get_matterport_id();

	if ( empty( $matterport_id ) ) {
		return '';
	}

	$class_names = get_names( [
		'property-gallery__button',
		'property-gallery__button--360',
		$options['class'],
	]);

	$text_domain = defined( 'YSD_MAPI_TEXT_DOMAIN' ) ? YSD_MAPI_TEXT_DOMAIN : SUNSET_REALTORS_PLUGIN_DOMAIN;

	return sprintf(
		'<a href="#matterport360" class="%s">
			<span>360&#176; %s</span>
		</a>',
		esc_attr( $class_names ),
		esc_html__( 'Pictures', $text_domain ),
	);
}

/**
 * Get the matterport ID of the current property.
 *
 * @return string|null The matterport ID of the current property.
 */
function get_matterport_id(): ?string {
	$post_id = get_queried_object_id();
	$matterport_id = $post_id ? (string) get_post_meta( $post_id, Listing_Meta::META_MATTERPORT_ID, true ) : null;

	return $matterport_id ?? null;
}
