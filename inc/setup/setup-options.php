<?php

/**
 * Admin settings page.
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Setup\Admin;

use TAB\Sunset_Realtors\Currency\Rates_Service;
use TAB\Sunset_Realtors\Listing\Listing_Gravity_Forms;

add_action('admin_menu', __NAMESPACE__ . '\\register_menu');
add_action('admin_init', __NAMESPACE__ . '\\register_settings');

/**
 * @return void
 */
function register_menu(): void
{
    add_options_page(
        __('Sunset Realtors', SUNSET_REALTORS_PLUGIN_DOMAIN),
        __('Sunset Realtors', SUNSET_REALTORS_PLUGIN_DOMAIN),
        'manage_options',
        'sunset-realtors-settings',
        __NAMESPACE__ . '\\render_page'
    );
}

/**
 * @return void
 */
function register_settings(): void
{
    register_setting('sunset_realtors', Rates_Service::OPTION_API_KEY, [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ]);

    register_setting('sunset_realtors', Listing_Gravity_Forms::OPTION_DEFAULT_EMAIL, [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ]);
}

/**
 * @return void
 */
function render_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Sunset Realtors', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('sunset_realtors'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sunset-exchangerate-api-key"><?php esc_html_e('ExchangeRate-API key', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input type="password" id="sunset-exchangerate-api-key" name="<?php echo esc_attr(Rates_Service::OPTION_API_KEY); ?>" value="<?php echo esc_attr((string) get_option(Rates_Service::OPTION_API_KEY, '')); ?>" class="regular-text" autocomplete="off" <?php echo defined('SUNSET_EXCHANGERATE_API_KEY') && '' !== SUNSET_EXCHANGERATE_API_KEY ? 'disabled' : ''; ?>>
                        <p class="description"><?php esc_html_e('API key van exchangerate-api.com voor EUR/USD/XCG koersen. Of definieer SUNSET_EXCHANGERATE_API_KEY in wp-config.php.', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sunset-default-email"><?php esc_html_e('Default email', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input type="email" id="sunset-default-email" name="<?php echo esc_attr(Listing_Gravity_Forms::OPTION_DEFAULT_EMAIL); ?>" value="<?php echo esc_attr((string) get_option(Listing_Gravity_Forms::OPTION_DEFAULT_EMAIL, '')); ?>" class="regular-text" autocomplete="off">
                        <p class="description"><?php esc_html_e('De standaard email die gebruikt wordt als er geen medewerker is gekoppeld aan de property.', SUNSET_REALTORS_PLUGIN_DOMAIN); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
