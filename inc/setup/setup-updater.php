<?php

/**
 * Setup Updater
 * https://github.com/YahnisElsts/plugin-update-checker
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Setup\Updater;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

PucFactory::buildUpdateChecker(
	'https://tabitdevelopment.nl/packages/plugins/sunset-realtors-plugin.json',
	SUNSET_REALTORS_PLUGIN_DIR_PATH . '/sunset-realtors-plugin.php',
	SUNSET_REALTORS_PLUGIN_DOMAIN
);
