<?php

/**
 * Setup meta boxes
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Meta\Setup;

add_action('save_post', __NAMESPACE__ . '\\save_meta_boxes');

/**
 * Store meta box configurations
 *
 * @var array
 */
$GLOBALS['ysl_meta_configs'] = [];

/**
 * Store meta box configurations without registering UI.
 *
 * @param array $meta_boxes Array of meta box configurations.
 * @return void
 */
function register_meta_configs(array $meta_boxes = []): void
{
    if (empty($meta_boxes)) {
        return;
    }

    foreach ($meta_boxes as $id => $args) {
        $GLOBALS['ysl_meta_configs'][$id] = wp_parse_args(
            $args,
            [
                'post_type' => 'post',
                'title'     => __('Meta Box', 'sunset-realtors-plugin'),
                'context'   => 'side',
                'priority'  => 'default',
                'fields'    => [],
            ]
        );
    }
}

/**
 * Register multiple custom meta boxes
 *
 * @param array $meta_boxes Array of meta box configurations.
 * @return void
 */
function register_custom_meta(array $meta_boxes = []): void
{

    if (empty($meta_boxes)) {
        return;
    }

    register_meta_configs($meta_boxes);

    foreach ($meta_boxes as $id => $args) {
        $args = $GLOBALS['ysl_meta_configs'][$id] ?? $args;

        // Register meta box.
        add_meta_box(
            $id . '_meta_box',
            $args['title'],
            function (\WP_Post $post) use ($id, $args) {
                render_meta_box($post, $id, $args);
            },
            $args['post_type'],
            $args['context'],
            $args['priority']
        );
    }
}

/**
 * Render meta box
 *
 * @param \WP_Post $post The post object.
 * @param string   $id   The meta box ID.
 * @param array    $args The meta box arguments.
 * @return void
 */
function render_meta_box(\WP_Post $post, string $id, array $args): void
{
    $output  = '';
    $post_id = $post->ID;

    // Nonce field.
    wp_nonce_field($id . '_meta_box', $id . '_meta_box_nonce');

    // Fields.
    $fields = $args['fields'] ?? [];
    foreach ($fields as $field_key => $field_args) {
        $field_type = $field_args['type'] ?? 'text';

        if ('link' === $field_type) {
            $output .= render_link_field($post_id, $field_key, $field_args);
            continue;
        }

        // Get field arguments.
        $field_label       = $field_args['label'] ?? '';
        $field_placeholder = $field_args['placeholder'] ?? '';
        $field_description = $field_args['description'] ?? '';
        $field_required    = $field_args['required'] ?? false;

        // Get field value.
        $field_value = get_post_meta($post_id, $field_key, true);

        // Render field.
        $output .= sprintf(
            '<div class="c-meta-box__field">
				<label class="c-meta-box__label" for="meta-%1$s">%2$s</label>
				<input class="c-meta-box__input" id="meta-%1$s"
					name="%1$s"
					value="%3$s"
					placeholder="%4$s"
					type="%5$s"
					%7$s
				>
				<p class="c-meta-box__description">%6$s</p>
			</div>',
            esc_attr($field_key),
            esc_html($field_label),
            esc_attr($field_value),
            esc_attr($field_placeholder),
            esc_attr($field_type),
            esc_html($field_description),
            $field_required ? 'required' : ''
        );
    }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Field values escaped above.
    echo sprintf(
        '<div class="c-meta-box__fields">%s</div>',
        $output
    );
}

/**
 * Save meta boxes
 *
 * @param int $post_id The post ID.
 * @return void
 */
function save_meta_boxes(int $post_id): void
{
    if (empty($GLOBALS['ysl_meta_configs'])) {
        return;
    }

    // Get meta box configurations.
    $ysl_meta_configs = $GLOBALS['ysl_meta_configs'];

    // Check post type.
    $post_type = get_post_type($post_id);
    if (! $post_type) {
        return;
    }

    // Loop through all registered meta boxes.
    foreach ($ysl_meta_configs as $id => $config) {
        // Only process meta boxes for this post type.
        if ($config['post_type'] !== $post_type) {
            continue;
        }

        $nonce_name = $id . '_meta_box_nonce';

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified below.
        if (! isset($_POST[$nonce_name])) {
            continue;
        }

        if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_name])), $id . '_meta_box')) {
            continue;
        }

        // Check autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            continue;
        }

        // Check permissions.
        if (! current_user_can('edit_post', $post_id)) {
            continue;
        }

        // Save fields.
        $fields = $config['fields'] ?? [];
        foreach ($fields as $field_key => $field_args) {
            $field_type = $field_args['type'] ?? 'text';

            if ('link' === $field_type) {
                save_link_field($post_id, $field_key);
                continue;
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
            if (isset($_POST[$field_key]) && '' !== $_POST[$field_key]) {
                update_post_meta(
                    $post_id,
                    $field_key,
                    sanitize_text_field(wp_unslash($_POST[$field_key]))
                );
            } else {
                delete_post_meta($post_id, $field_key);
            }
        }
    }
}

/**
 * Render a link field (URL, label, target and rel checkboxes).
 *
 * @param int                  $post_id    Post ID.
 * @param string               $field_key  Meta key.
 * @param array<string, mixed> $field_args Field configuration.
 * @return string
 */
function render_link_field(int $post_id, string $field_key, array $field_args): string
{
    $field_label       = $field_args['label'] ?? '';
    $field_description = $field_args['description'] ?? '';
    $link              = parse_link_meta((string) get_post_meta($post_id, $field_key, true));

    $output = sprintf(
        '<div class="c-meta-box__field c-meta-box__field--link">
			<p class="c-meta-box__label">%1$s</p>
			<div class="c-meta-box__link-fields">
				<label class="c-meta-box__sublabel" for="meta-%2$s-url">%3$s</label>
				<input class="c-meta-box__input" id="meta-%2$s-url" name="%2$s_url" value="%4$s" type="url" placeholder="%5$s">
				<label class="c-meta-box__sublabel" for="meta-%2$s-title">%6$s</label>
				<input class="c-meta-box__input" id="meta-%2$s-title" name="%2$s_title" value="%7$s" type="text" placeholder="%8$s">
				<div class="c-meta-box__checkboxes">
					<label class="c-meta-box__checkbox">
						<input type="checkbox" name="%2$s_target" value="_blank"%9$s>
						%10$s
					</label>
					<label class="c-meta-box__checkbox">
						<input type="checkbox" name="%2$s_nofollow" value="nofollow"%11$s>
						%12$s
					</label>
				</div>
			</div>
			<p class="c-meta-box__description">%13$s</p>
		</div>',
        esc_html($field_label),
        esc_attr($field_key),
        esc_html__('URL', 'sunset-realtors-plugin'),
        esc_attr($link['url']),
        esc_attr__('https://example.com', 'sunset-realtors-plugin'),
        esc_html__('Link text', 'sunset-realtors-plugin'),
        esc_attr($link['title']),
        esc_attr__('Button label', 'sunset-realtors-plugin'),
        checked($link['target'], '_blank', false),
        esc_html__('Open in new tab', 'sunset-realtors-plugin'),
        checked($link['rel'], 'nofollow', false),
        esc_html__('Add nofollow', 'sunset-realtors-plugin'),
        esc_html($field_description)
    );

    return $output;
}

/**
 * Save a link field to post meta.
 *
 * @param int    $post_id   Post ID.
 * @param string $field_key Meta key.
 * @return void
 */
function save_link_field(int $post_id, string $field_key): void
{
    $post_data = $_POST ?? [];

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in save_meta_boxes().
    $url = isset($_POST[$field_key . '_url'])
        ? esc_url_raw(wp_unslash($post_data[$field_key . '_url']))
        : '';

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in save_meta_boxes().
    $title = isset($_POST[$field_key . '_title'])
        ? sanitize_text_field(wp_unslash($post_data[$field_key . '_title']))
        : '';

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in save_meta_boxes().
    $target = isset($_POST[$field_key . '_target'])
        ? sanitize_text_field(wp_unslash($post_data[$field_key . '_target']))
        : '';

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in save_meta_boxes().
    $rel = isset($_POST[$field_key . '_nofollow'])
        ? sanitize_text_field(wp_unslash($post_data[$field_key . '_nofollow']))
        : '';

    if ('' === $url) {
        delete_post_meta($post_id, $field_key);
        return;
    }

    update_post_meta(
        $post_id,
        $field_key,
        build_link_meta($url, $title, $target, $rel)
    );
}
