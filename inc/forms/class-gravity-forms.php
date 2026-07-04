<?php

/**
 * Gravity Forms inquiry email routing.
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Forms;

use TAB\Sunset_Realtors\Listing\Realtor;

final class Gravity_Forms
{
    public const CC_EMAIL = 'info@sunset-realtors.com';

    public const OPTION_FORM_ID = 'sunset_gf_inquiry_form_id';

    /**
     * @return void
     */
    public static function init(): void
    {
        if (! class_exists('GFForms')) {
            return;
        }

        add_filter('gform_field_value_property_id', [self::class, 'populate_property_id']);
        add_filter('gform_notification', [self::class, 'route_notification'], 10, 3);
        add_filter('gform_pre_render', [self::class, 'maybe_add_hidden_field']);
        add_filter('gform_pre_validation', [self::class, 'maybe_add_hidden_field']);
        add_filter('gform_pre_submission_filter', [self::class, 'maybe_add_hidden_field']);
        add_filter('gform_admin_pre_render', [self::class, 'maybe_add_hidden_field']);
    }

    /**
     * @return string
     */
    public static function populate_property_id(): string
    {
        if (! is_singular('property')) {
            return '';
        }

        return (string) get_queried_object_id();
    }

    /**
     * @param array<string, mixed> $form Form data.
     * @return array<string, mixed>
     */
    public static function maybe_add_hidden_field(array $form): array
    {
        $configured_id = absint(get_option(self::OPTION_FORM_ID, 0));

        if ($configured_id > 0 && (int) $form['id'] !== $configured_id) {
            return $form;
        }

        if ($configured_id === 0) {
            return $form;
        }

        if (! self::form_has_property_field($form)) {
            $form['fields'][] = \GF_Fields::create(
                [
                    'type'              => 'hidden',
                    'label'             => 'Property ID',
                    'id'                => self::get_property_field_id($form),
                    'formId'            => $form['id'],
                    'allowsPrepopulate' => true,
                    'inputName'         => 'property_id',
                ]
            );
        }

        return $form;
    }

    /**
     * @param array<string, mixed> $notification Notification config.
     * @param array<string, mixed> $form         Form data.
     * @param array<string, mixed> $entry        Entry data.
     * @return array<string, mixed>
     */
    public static function route_notification(array $notification, array $form, array $entry): array
    {
        $configured_id = absint(get_option(self::OPTION_FORM_ID, 0));

        if ($configured_id > 0 && (int) $form['id'] !== $configured_id) {
            return $notification;
        }

        $property_id = self::get_property_id_from_entry($form, $entry);

        if ($property_id <= 0) {
            return $notification;
        }

        $realtor_email = Realtor::get_employee_email($property_id);

        if ('' !== $realtor_email && is_email($realtor_email)) {
            $notification['to'] = $realtor_email;
        }

        if (! empty($notification['to']) && self::CC_EMAIL !== $notification['to']) {
            $notification['bcc'] = self::append_bcc($notification['bcc'] ?? '', self::CC_EMAIL);
        } elseif ('' === (string) ($notification['to'] ?? '')) {
            $notification['to'] = self::CC_EMAIL;
        }

        return $notification;
    }

    /**
     * @param array<string, mixed> $form  Form data.
     * @param array<string, mixed> $entry Entry data.
     * @return int
     */
    private static function get_property_id_from_entry(array $form, array $entry): int
    {
        foreach ($form['fields'] as $field) {
            if ('property_id' === ($field->inputName ?? '') || 'property_id' === ($field->adminLabel ?? '')) {
                return absint(rgar($entry, (string) $field->id));
            }
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $form Form data.
     * @return bool
     */
    private static function form_has_property_field(array $form): bool
    {
        foreach ($form['fields'] as $field) {
            if ('property_id' === ($field->inputName ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $form Form data.
     * @return int
     */
    private static function get_property_field_id(array $form): int
    {
        $max_id = 0;

        foreach ($form['fields'] as $field) {
            $max_id = max($max_id, (int) $field->id);
        }

        return $max_id + 1;
    }

    /**
     * @param string $existing Existing BCC addresses.
     * @param string $email    Email to append.
     * @return string
     */
    private static function append_bcc(string $existing, string $email): string
    {
        $addresses = array_filter(array_map('trim', explode(',', $existing)));

        if (! in_array($email, $addresses, true)) {
            $addresses[] = $email;
        }

        return implode(', ', $addresses);
    }
}
