<?php

/**
 * Setup Activation
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Setup\Activation;

/**
 * Plugin Activation
 *
 * @return void
 */
function plugin_activation(): void
{
    \flush_rewrite_rules();
}
