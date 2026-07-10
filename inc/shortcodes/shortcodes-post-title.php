<?php

/**
 * Shortcodes - Post Title
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Shortcodes\Post_Title;

use function TAB\Sunset_Realtors\Helpers\General\get_names;

add_shortcode('sunset_post_title', __NAMESPACE__ . '\\sunset_post_title_shortcode');
function sunset_post_title_shortcode($args = []): string
{
	$options = shortcode_atts(
		array(
			'tag' => 'span',
			'type' => '',
			'class'	=> '',
		),
		$args
	);

	$post_id     = 'single' === $options['type'] ? get_queried_object_id() : get_the_ID();
	$post_title  = get_the_title($post_id);

	$class = get_names([
		'c-property__post-title',
		$options['class'],
	]);

	return sprintf(
		'<%1$s class="c-property__post-title %2$s">%3$s</%1$s>',
		tag_escape( $options['tag'] ),
		esc_attr( $class ),
		$post_title
	);
}
