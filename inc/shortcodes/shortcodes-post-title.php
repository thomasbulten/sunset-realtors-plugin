<?php

/**
 * Shortcodes - Post Title
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Shortcodes\Post_Title;

add_shortcode('sunset_post_title', __NAMESPACE__ . '\\sunset_post_title_shortcode');
function sunset_post_title_shortcode($args = []): string
{
	$options = shortcode_atts(
		array(
			'tag'	=> 'span',
			'class'	=> '',
		),
		$args
	);

	$post_id	= get_queried_object_id();
	$post_type  = get_post_type($post_id);
	$post_id    = 'property' === $post_type ? $post_id : get_the_ID();
	$post_title = get_the_title($post_id);

	return sprintf(
		'<%1$s class="c-property__post-title %2$s">%3$s</%1$s>',
		$options['tag'],
		$options['class'],
		$post_title
	);
}
