<?php

/**
 * Setup taxonomies
 *
 * @package BP_Plugin
 */

declare(strict_types=1);

namespace BP\Plugin\Setup\Taxonomy;

add_action('init', __NAMESPACE__ . '\\register_taxonomy');

/**
 * Register taxonomies
 *
 * @return void
 */
function register_taxonomy(): void
{
    /**
     * Team Member Category
     */
    $talk_category_args = apply_filters('bp_team_member_category_args', [
        'label'             => __('Categories', BP_PLUGIN_DOMAIN),
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'show_in_nav_menus' => false,
        'rewrite'           => [
            'slug'       => apply_filters('bpp_team_member_category_slug', 'team-member-category'),
            'with_front' => false,
        ],
    ]);

    \register_taxonomy(
        'team_member_category',
        'team_member',
        $talk_category_args
    );
}
