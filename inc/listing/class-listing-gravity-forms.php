<?php

/**
 * Gravity Forms using the employee email.
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Listing;

final class Listing_Gravity_Forms
{

	/** @var string The option name. */
	public const OPTION_DEFAULT_EMAIL = 'sunset_realtors_default_email';

	/**
	 * @return void
	 */
	public static function init(): void
	{
		if (! class_exists('GFForms')) {
			return;
		}

		add_filter('gform_field_value_employee_email', [self::class, 'populate_employee_mail']);
	}

	/**
	 * @return string
	 */
	public static function populate_employee_mail(): string
	{
		$post_id   = get_queried_object_id();
		$post_type = get_post_type($post_id);

		if ( empty($post_id) || 'property' !== $post_type ) {
			return self::get_default_email();
		}

		$employee_ids = Listing_Meta::get_assigned_employee_ids( (int) $post_id );

		if ( empty( $employee_ids ) ) {
			return self::get_default_email();
		}

		$emails = [];

		foreach ( $employee_ids as $employee_id ) {
			$employee_email = get_post_meta( $employee_id, '_employee_email_address', true );

			if ( ! empty( $employee_email ) ) {
				$emails[] = $employee_email;
			}
		}

		if ( empty( $emails ) ) {
			return self::get_default_email();
		}

		$emails[] = self::get_default_email();

		return implode( ',', array_unique( $emails ) );
	}

	/**
	 * @param int $employee_id The employee ID.
	 * @return string The employee email.
	 */
	public static function get_employee_email( int $employee_id ): string
	{
		// Get the employee email from the post meta.
		$employee_email = get_post_meta($employee_id, '_employee_email_address', true);
		if ( ! $employee_email ) {
			return self::get_default_email();
		}

		// Always add the default email.
		$employee_email = $employee_email . ',' . self::get_default_email();

		return $employee_email;
	}

	/**
	 * @return string The default email.
	 */
	public static function get_default_email(): string
	{
		return get_option(self::OPTION_DEFAULT_EMAIL, 'info@sunset-realtors.com');
	}
}
