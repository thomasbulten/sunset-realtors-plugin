<?php

/**
 * Plugin Name: BP Plugin
 * Plugin URI: https://tabitdevelopment.nl
 * Description: The Boilerplate for WordPress Plugins.
 * Author: TAB IT Development
 * Author URI: https://tabitdevelopment.nl
 * Text Domain: bp-plugin
 * Version: 1.0.0
 *
 * @package BP_Plugin
 */

declare(strict_types=1);

namespace BP\Plugin;

define('BP_PLUGIN_VERSION', '1.0.0');
define('BP_PLUGIN_DOMAIN', 'bp-plugin');
define('BP_PLUGIN_DIR_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('BP_PLUGIN_DIR_URL', untrailingslashit(plugin_dir_url(__FILE__)));

define('BP_PLUGIN_BLOCKS_LIST', []);

define('BP_PLUGIN_UPDATE_URL', 'https://tabitdevelopment.nl/packages');
define('BP_PLUGIN_UPDATE_FOLDER', 'bp-plugin');
define('BP_PLUGIN_SLUG', 'bp-plugin');

require_once __DIR__ . '/inc/helpers/helpers.php';
require_once __DIR__ . '/inc/meta/meta.php';
require_once __DIR__ . '/inc/setup/setup.php';


/**
 * Load the plugin text domain.
 */
function load_domain()
{
    load_plugin_textdomain('bp-plugin', false, basename(__DIR__) . '/lang/');
}

add_action('init', __NAMESPACE__ . '\\register_blocks');

/**
 * Register blocks
 */
function register_blocks()
{
    $blocks = BP_PLUGIN_BLOCKS_LIST;

    foreach ($blocks as $block) {

        $args = [];

        if (file_exists(BP_PLUGIN_DIR_PATH . '/build/block-library/' . $block . '/template.php')) {
            $args['render_callback'] = function ($args, $inner_blocks) use ($block) {
                return apply_filters('render_callback_' . $block, $args, $inner_blocks);
            };
        }

        // Include all template files.
        if (file_exists(BP_PLUGIN_DIR_PATH . '/build/block-library/' . $block . '/template.php')) {
            require_once BP_PLUGIN_DIR_PATH . '/build/block-library/' . $block . '/template.php';
        }

        \register_block_type(BP_PLUGIN_DIR_PATH . '/build/block-library/' . $block . '/', $args);

        // Only WP 6.7 and above support this function.
        if (version_compare(get_bloginfo('version'), '6.7', '>=')) {
            if (file_exists(BP_PLUGIN_DIR_PATH . '/build/blocks-manifest.php')) {
                wp_register_block_metadata_collection(
                    BP_PLUGIN_DIR_PATH . '/build',
                    BP_PLUGIN_DIR_PATH . '/build/blocks-manifest.php'
                );
            }
        }
    }
}
