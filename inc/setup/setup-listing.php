<?php

/**
 * Listing modules bootstrap
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Setup\Listing;

use TAB\Sunset_Realtors\Currency\Currency_Display;
use TAB\Sunset_Realtors\Forms\Gravity_Forms;
use TAB\Sunset_Realtors\Listing\Listing_Meta;
use TAB\Sunset_Realtors\Listing\Location;
use TAB\Sunset_Realtors\Listing\Realtor;

require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/listing/class-listing-meta.php';
require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/listing/class-location.php';
require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/listing/class-realtor.php';
require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/forms/class-gravity-forms.php';
require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/currency/class-rates-service.php';
require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/currency/class-converter.php';
require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/currency/class-currency-display.php';

add_action('init', __NAMESPACE__ . '\\init_modules');

/**
 * Initialize listing modules.
 *
 * @return void
 */
function init_modules(): void
{
	Listing_Meta::init();
	Location::init();
	Realtor::init();
	Gravity_Forms::init();
	Currency_Display::init();
}
