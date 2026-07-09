<?php

/**
 * Listing modules bootstrap
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Setup\Listing;

use TAB\Sunset_Realtors\Currency\Currency_Display;
use TAB\Sunset_Realtors\Listing\Listing_Meta;
use TAB\Sunset_Realtors\Listing\Listing_Location;
use TAB\Sunset_Realtors\Listing\Listing_Gravity_Forms;

require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/listing/class-listing-meta.php';
require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/listing/class-listing-location.php';
require_once SUNSET_REALTORS_PLUGIN_DIR_PATH . '/inc/listing/class-listing-gravity-forms.php';
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
	Currency_Display::init();
	Listing_Meta::init();
	Listing_Location::init();
	Listing_Gravity_Forms::init();
}
