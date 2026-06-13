<?php

/**
 * Setup Assets
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Setup\Assets;

use function TAB\Sunset_Realtors\Helpers\Settings\get_frontend_strings;

// Setup Assets.
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\main_assets');

// Setup Admin Assets.
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_assets');

// Setup module type attribute
add_filter('script_loader_tag', __NAMESPACE__ . '\\add_module_type', 10, 3);

/**
 * Main assets
 *
 * @return void
 */
function main_assets(): void
{
    enqueue_vite_assets('main');
    localize_main_assets();
}

/**
 * Admin assets
 *
 * @return void
 */
function admin_assets(): void
{
    enqueue_vite_assets('admin');
}

/**
 * Pass frontend strings to the store locator script.
 *
 * @return void
 */
function localize_main_assets(): void
{
    $strings = get_frontend_strings();

    wp_add_inline_script(
        'sunset-realtors-plugin-main',
        'window.yslStoreLocator = ' . wp_json_encode(['strings' => $strings]) . ';',
        'before'
    );
}

/**
 * Get uri to asset file
 *
 * @param  string $file Filename.
 * @return string
 */
function get_file_uri(string $file): string
{
    return SUNSET_REALTORS_PLUGIN_DIR_URL . '/build/' . $file;
}

/**
 * Get file path
 *
 * @param  string $file Filename.
 * @return string
 */
function get_file_path(string $file): string
{
    return SUNSET_REALTORS_PLUGIN_DIR_PATH . '/build/' . $file;
}

/**
 * Check if Vite dev server is running
 *
 * @return bool
 */
function is_vite_dev_server(): bool
{
    $manifest_path = get_file_path('assets/manifest.json');
    return ! file_exists($manifest_path);
}

/**
 * Get Vite dev server URL
 *
 * @return string
 */
function get_vite_dev_server_url(): string
{
    return 'http://localhost:5173';
}

/**
 * Enqueue Vite assets
 *
 * @param  string $entry Entry point name (main or admin).
 * @return void
 */
function enqueue_vite_assets(string $entry): void
{
    if (is_vite_dev_server()) {
        // Development mode - use Vite dev server
        $dev_url = get_vite_dev_server_url();

        // Enqueue Vite client for HMR (must be a module)
        wp_enqueue_script_module(
            'sunset-realtors-plugin-vite-client',
            $dev_url . '/@vite/client',
            [],
            null
        );

        // Enqueue entry point
        wp_enqueue_script_module(
            'sunset-realtors-plugin-' . $entry,
            $dev_url . '/assets/js/' . $entry . '.js',
            ['sunset-realtors-plugin-vite-client'],
            null
        );
    } else {
        // Production mode - use manifest
        $manifest_path = get_file_path('assets/manifest.json');

        if (! file_exists($manifest_path)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifest_path), true);
        $entry_file = 'assets/js/' . $entry . '.js';

        if (! isset($manifest[$entry_file])) {
            return;
        }

        $entry_data = $manifest[$entry_file];

        // Enqueue CSS
        if (isset($entry_data['css'])) {
            foreach ($entry_data['css'] as $css_file) {
                wp_enqueue_style(
                    'sunset-realtors-plugin-' . $entry . '-styles',
                    get_file_uri('assets/' . $css_file),
                    [],
                    SUNSET_REALTORS_PLUGIN_VERSION
                );
            }
        }

        // Enqueue JS
        wp_enqueue_script_module(
            'sunset-realtors-plugin-' . $entry,
            get_file_uri('assets/' . $entry_data['file']),
            [],
            SUNSET_REALTORS_PLUGIN_VERSION
        );
    }
}

/**
 * Add type="module" to script tags for Vite modules
 *
 * @param  string $tag    The script tag.
 * @param  string $handle The script handle.
 * @param  string $src    The script source.
 * @return string
 */
function add_module_type(string $tag, string $handle, string $src): string
{
    // Only add module type to our Vite scripts
    if (
        0 === strpos($handle, 'sunset-realtors-plugin-') &&
        (is_vite_dev_server() || false !== strpos($src, '/build/assets/'))
    ) {
        // Check if type attribute already exists
        if (false === strpos($tag, 'type=')) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        } elseif (false === strpos($tag, 'type="module"') && false === strpos($tag, "type='module'")) {
            // Replace existing type attribute with module
            $tag = preg_replace('/type=["\'][^"\']*["\']/', 'type="module"', $tag);
        }
    }

    return $tag;
}
