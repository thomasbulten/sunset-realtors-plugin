<?php

/**
 * Setup post types
 *
 * @package BP_Plugin
 */

declare(strict_types=1);

namespace BP\Plugin\Setup\Assets;

// Setup Assets.
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\main_assets');

// Setup Admin Assets.
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_assets');

// Setup Meta Fields.
add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\\meta_settings');

/**
 * Main assets
 *
 * @return void
 */
function main_assets(): void
{
    // Styles.
    wp_enqueue_style(
        'bp-plugin-styles',
        plugins_url('../../assets/css/style.css', __FILE__),
        [],
        BP_PLUGIN_VERSION
    );

    // Scripts.
    wp_enqueue_script_module(
        'bp-plugin-scripts',
        plugins_url('../../assets/js/script.js', __FILE__),
        [],
        BP_PLUGIN_VERSION,
        true
    );
}

/**
 * Admin assets
 *
 * @return void
 */
function admin_assets(): void
{
    // Styles.
    wp_enqueue_style(
        'bp-plugin-admin-styles',
        plugins_url('../../assets/css/admin.css', __FILE__),
        [],
        BP_PLUGIN_VERSION
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
    return BP_PLUGIN_DIR_URL . '/build/' . $file;
}

/**
 * Get file path
 *
 * @param  string $file Filename.
 * @return string
 */
function get_file_path(string $file): string
{
    return BP_PLUGIN_DIR_PATH . '/build/' . $file;
}

/**
 * Meta settings
 *
 * @return void
 */
function meta_settings(): void
{

    // Meta.
    $meta_deps_path = get_file_path('/meta-fields/meta-fields.assets.php');
    $meta_path      = get_file_path('meta-fields/meta-fields.js');

    $meta_script_dependencies = file_exists($meta_deps_path) ?
        require $meta_deps_path :
        [
            'dependencies' => [],
            'version'      => filemtime($meta_path),
        ];

    \wp_register_script(
        'bp-plugin-meta-fields',
        get_file_uri('meta-fields/meta-fields.js'),
        $meta_script_dependencies['dependencies'],
        $meta_script_dependencies['version'],
        true
    );

    \wp_enqueue_script('bp-plugin-meta-fields');
}
