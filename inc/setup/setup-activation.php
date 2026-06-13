<?php

/**
 * Setup Activation
 *
 * @package BP_Plugin
 */

declare(strict_types=1);

namespace BP\Plugin\Setup\Activation;

register_activation_hook(__FILE__, __NAMESPACE__ . '\\plugin_activation');
register_activation_hook(__FILE__, __NAMESPACE__ . '\\plugin_deactivation');

/**
 * Plugin Activation
 *
 * @return void
 */
function plugin_activation(): void
{
    BP\Plugin\Setup\PostType\register_post_type();
    BP\Plugin\Setup\Taxonomy\register_taxonomy();
    \flush_rewrite_rules();
}

/**
 * Plugin Deactivation
 *
 * @return void
 */
function plugin_deactivation(): void
{
    \unregister_post_type('team_member');
}
