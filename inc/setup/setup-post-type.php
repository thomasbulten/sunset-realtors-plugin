<?php

/**
 * Setup post types
 *
 * @package BP_Plugin
 */

declare(strict_types=1);

namespace BP\Plugin\Setup\PostType;

add_action('init', __NAMESPACE__ . '\\register_post_type');

/**
 * Register post types
 *
 * @return void
 */
function register_post_type()
{
    $labels = [
        'name'               => _x('Team Members', 'post type general name', BP_PLUGIN_DOMAIN),
        'singular_name'      => _x('Team Member', 'post type singular name', BP_PLUGIN_DOMAIN),
        'menu_name'          => _x('Team Members', 'admin menu', BP_PLUGIN_DOMAIN),
        'name_admin_bar'     => _x('Team Member', 'add new on admin bar', BP_PLUGIN_DOMAIN),
        'add_new'            => _x('Add New', BP_PLUGIN_DOMAIN),
        'add_new_item'       => __('Add New', BP_PLUGIN_DOMAIN),
        'new_item'           => __('New Team Member', BP_PLUGIN_DOMAIN),
        'edit_item'          => __('Edit Team Member', BP_PLUGIN_DOMAIN),
        'view_item'          => __('View Team Member', BP_PLUGIN_DOMAIN),
        'all_items'          => __('All Team Members', BP_PLUGIN_DOMAIN),
        'search_items'       => __('Search Team Member', BP_PLUGIN_DOMAIN),
        'parent_item_colon'  => __('Parent Team Member:', BP_PLUGIN_DOMAIN),
        'not_found'          => __('No team member found.', BP_PLUGIN_DOMAIN),
        'not_found_in_trash' => __('No team member found in Trash.', BP_PLUGIN_DOMAIN),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'team'],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_rest'       => false,
        'menu_icon'          => 'dashicons-groups',
        'supports'           => ['title', 'author', 'thumbnail'],
    ];

    \register_post_type('team_member', apply_filters('bpp_team_member_post_type_args', $args));
}
