<?php

/**
 * Setup Options
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Setup\Options;

use function TAB\Sunset_Realtors\Helpers\Settings\get_default_settings;
use function TAB\Sunset_Realtors\Helpers\Settings\get_plugin_settings;
use function TAB\Sunset_Realtors\Helpers\Settings\sanitize_settings;
use function TAB\Sunset_Realtors\Helpers\Settings\schedule_rewrite_flush;
use function TAB\Sunset_Realtors\Helpers\Store\update_stores_cache;
use const TAB\Sunset_Realtors\Helpers\Settings\SETTINGS_OPTION;
use const TAB\Sunset_Realtors\Helpers\Store\STORE_POST_TYPE;

add_action('admin_menu', __NAMESPACE__ . '\\register_settings_page');
add_action('admin_init', __NAMESPACE__ . '\\register_settings');
add_action('update_option_ysl_settings', __NAMESPACE__ . '\\refresh_stores_cache');

/**
 * Register settings submenu under the store post type.
 *
 * @return void
 */
function register_settings_page(): void
{
    add_submenu_page(
        'edit.php?post_type=' . STORE_POST_TYPE,
        __('Store Locator Settings', SUNSET_REALTORS_PLUGIN_DOMAIN),
        __('Settings', SUNSET_REALTORS_PLUGIN_DOMAIN),
        'manage_options',
        'ysl-settings',
        __NAMESPACE__ . '\\render_settings_page'
    );
}

/**
 * Register plugin settings.
 *
 * @return void
 */
function register_settings(): void
{
    register_setting(
        'ysl_settings_group',
        SETTINGS_OPTION,
        [
            'type'              => 'array',
            'sanitize_callback' => __NAMESPACE__ . '\\sanitize_settings_option',
            'default'           => get_default_settings(),
        ]
    );
}

/**
 * Sanitize settings option via Settings API.
 *
 * @param mixed $input Raw option value.
 * @return array<string, string>
 */
function sanitize_settings_option($input): array
{
    if (! is_array($input)) {
        return get_default_settings();
    }

    $old_settings = get_plugin_settings();
    $sanitized    = sanitize_settings($input);

    $rewrite_changed = ($old_settings['rewrite_slug'] ?? '') !== ($sanitized['rewrite_slug'] ?? '');
    $singles_changed = ($old_settings['enable_single_pages'] ?? '1') !== ($sanitized['enable_single_pages'] ?? '1');

    if ($rewrite_changed || $singles_changed) {
        schedule_rewrite_flush();
    }

    return $sanitized;
}

/**
 * Rebuild the stores API cache after settings are saved.
 *
 * @return void
 */
function refresh_stores_cache(): void
{
    update_stores_cache();
}

/**
 * Render settings page.
 *
 * @return void
 */
function render_settings_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $settings = get_plugin_settings();
    $defaults = get_default_settings();
?>

    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form action="options.php" method="post">
            <?php
            settings_fields('ysl_settings_group');
            ?>

            <h2><?php esc_html_e('Admin labels', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></h2>
            <p class="description">
                <?php esc_html_e('Customize how locations appear in the WordPress admin. The internal post type slug remains ysd_store.', SUNSET_REALTORS_PLUGIN_DOMAIN); ?>
            </p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="ysl_label_plural"><?php esc_html_e('Plural label', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ysl_label_plural"
                            name="<?php echo esc_attr(SETTINGS_OPTION); ?>[label_plural]"
                            value="<?php echo esc_attr($settings['label_plural']); ?>"
                            class="regular-text" />
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %s: default plural label */
                                esc_html__('Default: %s', SUNSET_REALTORS_PLUGIN_DOMAIN),
                                esc_html($defaults['label_plural'])
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ysl_label_singular"><?php esc_html_e('Singular label', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ysl_label_singular"
                            name="<?php echo esc_attr(SETTINGS_OPTION); ?>[label_singular]"
                            value="<?php echo esc_attr($settings['label_singular']); ?>"
                            class="regular-text" />
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %s: default singular label */
                                esc_html__('Default: %s', SUNSET_REALTORS_PLUGIN_DOMAIN),
                                esc_html($defaults['label_singular'])
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ysl_rewrite_slug"><?php esc_html_e('URL rewrite slug', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ysl_rewrite_slug"
                            name="<?php echo esc_attr(SETTINGS_OPTION); ?>[rewrite_slug]"
                            value="<?php echo esc_attr($settings['rewrite_slug']); ?>"
                            class="regular-text" />
                        <p class="description">
                            <?php
                            printf(
                                /* translators: 1: example URL path, 2: default slug */
                                esc_html__('Used in single location URLs, e.g. /%1$s/my-location/. Default: %2$s', SUNSET_REALTORS_PLUGIN_DOMAIN),
                                esc_html('' !== $settings['rewrite_slug'] ? $settings['rewrite_slug'] : $defaults['rewrite_slug']),
                                esc_html($defaults['rewrite_slug'])
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Single pages', SUNSET_REALTORS_PLUGIN_DOMAIN); ?>
                    </th>
                    <td>
                        <label for="ysl_enable_single_pages">
                            <input
                                type="checkbox"
                                id="ysl_enable_single_pages"
                                name="<?php echo esc_attr(SETTINGS_OPTION); ?>[enable_single_pages]"
                                value="1"
                                <?php checked($settings['enable_single_pages'], '1'); ?> />
                            <?php esc_html_e('Enable single location pages on the frontend', SUNSET_REALTORS_PLUGIN_DOMAIN); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('When disabled, locations are only shown in the store locator and cannot be visited as standalone pages.', SUNSET_REALTORS_PLUGIN_DOMAIN); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e('Frontend copy', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="ysl_search_label"><?php esc_html_e('Search label', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ysl_search_label"
                            name="<?php echo esc_attr(SETTINGS_OPTION); ?>[search_label]"
                            value="<?php echo esc_attr($settings['search_label']); ?>"
                            class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ysl_no_results_search"><?php esc_html_e('No results (search filter)', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ysl_no_results_search"
                            name="<?php echo esc_attr(SETTINGS_OPTION); ?>[no_results_search]"
                            value="<?php echo esc_attr($settings['no_results_search']); ?>"
                            class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ysl_no_results_empty"><?php esc_html_e('No results (empty list)', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ysl_no_results_empty"
                            name="<?php echo esc_attr(SETTINGS_OPTION); ?>[no_results_empty]"
                            value="<?php echo esc_attr($settings['no_results_empty']); ?>"
                            class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ysl_load_error"><?php esc_html_e('Load error', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ysl_load_error"
                            name="<?php echo esc_attr(SETTINGS_OPTION); ?>[load_error]"
                            value="<?php echo esc_attr($settings['load_error']); ?>"
                            class="regular-text" />
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e('Default button', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Default button', SUNSET_REALTORS_PLUGIN_DOMAIN); ?>
                    </th>
                    <td>
                        <label for="ysl_enable_default_button">
                            <input
                                type="checkbox"
                                id="ysl_enable_default_button"
                                name="<?php echo esc_attr(SETTINGS_OPTION); ?>[enable_default_button]"
                                value="1"
                                <?php checked($settings['enable_default_button'], '1'); ?> />
                            <?php esc_html_e('Show a default button linking to the single location page', SUNSET_REALTORS_PLUGIN_DOMAIN); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Disable this to use only the custom buttons configured per location.', SUNSET_REALTORS_PLUGIN_DOMAIN); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ysl_default_button_label"><?php esc_html_e('Default button label', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ysl_default_button_label"
                            name="<?php echo esc_attr(SETTINGS_OPTION); ?>[default_button_label]"
                            value="<?php echo esc_attr($settings['default_button_label']); ?>"
                            class="regular-text" />
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>

<?php
}
