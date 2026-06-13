<?php

/**
 * Setup Domain
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Setup\Domain;

add_action('init', __NAMESPACE__ . '\\load_domain');

/**
 * Load the plugin text domain.
 *
 * @return void
 */
function load_domain(): void
{
    load_plugin_textdomain(SUNSET_REALTORS_PLUGIN_DOMAIN, false, basename(SUNSET_REALTORS_PLUGIN_DIR_PATH) . '/languages/');
}
