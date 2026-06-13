<?php

/**
 * Plugin meta Page
 *
 * @package BP_Plugin
 */

declare(strict_types=1);

namespace BP\Plugin\Meta\Page;

add_action('init', __NAMESPACE__ . '\\register_post_meta');

/**
 * Register meta fields
 */
function register_post_meta()
{

    // Register common meta fields.
    $fields = [];

    foreach ($fields as $field) {
        register_post_meta(
            'page',
            '_bpp_page_' . $field,
            [
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => 'string',
                'auth_callback' => function () { // phpcs:ignore NeutronStandard.Functions.TypeHint.NoReturnType
                    return current_user_can('edit_posts');
                },
            ]
        );
    }
}
