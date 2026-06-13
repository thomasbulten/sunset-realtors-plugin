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

$my_updater = PucFactory::buildUpdateChecker(
	'https://github.com/thomasbulten/' . SUNSET_REALTORS_PLUGIN_DOMAIN . '/',
	SUNSET_REALTORS_PLUGIN_DIR_PATH . '/' . SUNSET_REALTORS_PLUGIN_DOMAIN . '.php',
	SUNSET_REALTORS_PLUGIN_DOMAIN
);

// Set the branch that contains the stable release.
$my_updater->setBranch('main');
