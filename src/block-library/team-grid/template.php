<?php

/**
 * Block template
 *
 * @package BP_Plugin
 */

declare(strict_types=1);

namespace BP\Plugin\TeamGrid\Template;

use function BP\Plugin\Helpers\get_names;
use function BP\Plugin\Helpers\Template\Team\get_team_template;

/**
 * Render callback template
 *
 * @param  array  $attributes Block attributes.
 * @param  string $blocks Block content.
 *
 * @return string
 */
function block_frontend_template(array $attributes, string $blocks): string
{

    $classes = get_names([
        'bpp-team-grid',
        ! empty($attributes['className']) ? $attributes['className'] : '',
    ]);

    $wrapper_attrs = [
        'class' => $classes,
    ];

    if (isset($attributes['anchor']) && ! empty($attributes['anchor'])) {
        $wrapper_attrs['id'] = esc_attr($attributes['anchor']);
    }

    $wrapper_attributes = get_block_wrapper_attributes(array_merge([], $wrapper_attrs));

    $input_text = sprintf(
        '<div class="bpp-team-grid__search">
            <h4>%s</h4>
            <div class="bpp-team-grid__search-field-wrapper">
                <input type="text" class="bpp-team-grid__search-field js-bpp-team-grid-search" placeholder="%s">
            </div>
        </div>',
        __('Zoek', BP_PLUGIN_DOMAIN),
        __('Type een naam', BP_PLUGIN_DOMAIN)
    );

    $post_list = posts_list($attributes);

    return sprintf(
        '<div %s>
            %s
            <div class="bpp-team-grid__container js-bpp-team-grid-show">%s</div>
            <div class="bpp-team-grid__staging js-bpp-team-grid-hide"></div>
        </div>',
        $wrapper_attributes,
        $input_text,
        $post_list
    );
}


/**
 * Create speakers grid item list
 *
 * @param array $attributes Arguments list.
 */
function posts_list(array $attributes = []): string
{

    // Setup args.
    $args = [
        'post_type'      => 'team_member',
        'posts_per_page' => -1,
    ];

    // Order type.
    if (isset($attributes['orderBy'])) {
        $args['orderby'] = $attributes['orderBy'];
    }

    // Order type.
    $args['order'] = isset($attributes['order']) ? $attributes['order'] : 'ASC';

    $posts_query = new \WP_Query($args);

    // Start Output buffer.
    ob_start();

    $count = 1;
    while ($posts_query->have_posts()) {
        $posts_query->the_post();

        echo get_team_template(get_the_ID(), $attributes, $count);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $count++;
    } // Endwhile.

    wp_reset_postdata();
    return ob_get_clean();
}

add_filter('render_callback_team-grid', __NAMESPACE__ . '\\block_frontend_template', 10, 2);
