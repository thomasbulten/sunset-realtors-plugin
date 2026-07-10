<?php
/**
 * Shortcodes - Employee
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Shortcodes\Employee;

use TAB\Sunset_Realtors\Listing\Listing_Meta;
use function TAB\Sunset_Realtors\Helpers\General\get_names;
use function TAB\Sunset_Realtors\Helpers\General\create_phone_link;
use function TAB\Sunset_Realtors\Helpers\General\create_mailto_link;

add_shortcode( 'sunset_employee', __NAMESPACE__ . '\\sunset_employee_shortcode' );

/**
 * Shortcode to display the employee of a property.
 *
 * @param array<string, string> $args The shortcode arguments.
 * @return string The employee of the property.
 */
function sunset_employee_shortcode( $args = [] ): string {
	$options = shortcode_atts( [
		'class' => '',
	], $args, 'sunset_employee' );

	$post_id = get_queried_object_id();
	$employees = get_employees_data( $post_id );
	if ( empty( $employees ) ) {
		return '';
	}

	$employees_html = '';

	foreach ( $employees as $employee ) {
		$employees_html .= get_employee_html( $employee );
	}

	$class = get_names( [
		'c-employee__list',
		$options['class'],
	] );

	return sprintf(
		'<ul class="%1$s" role="list">%2$s</ul>',
		esc_attr( $class ),
		$employees_html
	);
}

/**
 * Get the selected employees.
 *
 * @param int $post_id The post ID.
 * @return array<int, array<string, mixed>> The selected employee data.
 */
function get_employees_data( int $post_id ): array {
	$employee_ids = Listing_Meta::get_assigned_employee_ids( $post_id );

	if ( empty( $employee_ids ) ) {
		return [];
	}

	$employees = [];

	foreach ( $employee_ids as $employee_id ) {
		$post = get_post( $employee_id );

		if ( ! $post instanceof \WP_Post ) {
			continue;
		}

		$employees[] = [
			'name'     => $post->post_title ?? '',
			'image_id' => get_post_thumbnail_id( $post->ID ) ?? '',
			'phone'    => get_post_meta( $post->ID, '_employee_phone_number', true ) ?? '',
			'email'    => get_post_meta( $post->ID, '_employee_email_address', true ) ?? '',
		];
	}

	return $employees;
}

/**
 * Get the HTML for the employee.
 *
 * @param array $data The employee data.
 * @return string The HTML for the employee.
 */
function get_employee_html( array $data ): string {
	// If there is an image, add the image.
	$image = '';
	if ( ! empty( $data['image_id'] ) ) {
		$image_url    = wp_get_attachment_url( $data['image_id'] );
		$image_srcset = wp_get_attachment_image_srcset( $data['image_id'], 'medium' );
		$image_alt    = get_post_meta( $data['image_id'], '_wp_attachment_image_alt', true ) ?: $data['name'] ?? '';

		$image = sprintf(
			'<figure class="c-employee__image-wrapper">
				<img class="c-employee__image" src="%1$s" srcset="%2$s" alt="%3$s" width="200" height="200">
			</figure>',
			esc_url( $image_url ),
			esc_html( $image_srcset ),
			esc_html( $image_alt )
		);
	}

	// If there is phone number, add the phone link.
	$phone_link = '';
	if ( ! empty( $data['phone'] ) ) {
		$phone_link = sprintf(
			'<div class="c-employee__phone">
				<a href="%1$s">%2$s</a>
			</div>',
			create_phone_link( $data['phone'] ),
			esc_html( $data['phone'] )
		);
	}

	// If there is email address, add the mailto link.
	$email_link = '';
	if ( ! empty( $data['email'] ) ) {
		$email_link = sprintf(
			'<div class="c-employee__email">
				<a href="%1$s">%2$s</a>
			</div>',
			create_mailto_link( $data['email'] ),
			esc_html( $data['email'] )
		);
	}

	return sprintf(
		'<li class="c-employee__list-item" role="listitem">
			%2$s
			<div class="c-employee__content">
				<div class="c-employee__name"><strong>%1$s</strong></div>
				%3$s
				%4$s
			</div>
		</li>',
		esc_html( $data['name'] ),
		$image,
		$phone_link,
		$email_link
	);
}
