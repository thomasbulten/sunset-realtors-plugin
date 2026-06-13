<?php

/**
 * Plugin Name: Sunset Realtors Plugin
 * Plugin URI: https://tabitdevelopment.nl
 * Description: The Support Plugin for Sunset Realtors.
 * Author: TAB IT Development
 * Author URI: https://tabitdevelopment.nl
 * Text Domain: sunset-realtors-plugin
 * Version: 0.1.0
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors;

define('SUNSET_REALTORS_PLUGIN_VERSION', '0.1.0');
define('SUNSET_REALTORS_PLUGIN_DOMAIN', 'sunset-realtors-plugin');
define('SUNSET_REALTORS_PLUGIN_DIR_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('SUNSET_REALTORS_PLUGIN_DIR_URL', untrailingslashit(plugin_dir_url(__FILE__)));

// Setup Updater.
require_once __DIR__ . '/inc/updater/plugin-update-checker.php';

// Include Primary files.
require_once __DIR__ . '/inc/setup/setup.php';
require_once __DIR__ . '/inc/helpers/helpers.php';
require_once __DIR__ . '/inc/api/api.php';

// Include Secondary files.
require_once __DIR__ . '/inc/blocks/blocks.php';
require_once __DIR__ . '/inc/meta/meta.php';

// Register activation hook.
// register_activation_hook(__FILE__, 'TAB\Sunset_Realtors\Setup\Activation\plugin_activation');
