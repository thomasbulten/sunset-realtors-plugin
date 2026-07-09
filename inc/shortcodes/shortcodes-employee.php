<?php
/**
 * Shortcodes - Employee
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Shortcodes\Employee;

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
	$get_employee = get_employee_data( $post_id );
	if ( ! $get_employee ) {
		return '';
	}

	$get_employee_html = get_employee_html( $get_employee );

	$class = get_names( [
		'c-employee__list',
		$options['class'],
	], true );

	return sprintf(
		'<ul class="%1$s" role="list">%2$s</ul>',
		esc_attr( $class ),
		$get_employee_html
	);
}

/**
 * Get the selected employee.
 *
 * @param int $post_id The post ID.
 * @return array|null The selected employee data, or null if no employee is assigned.
 */
function get_employee_data( int $post_id ): ?array {
	// Get the employee ID from the post meta.
	$get_employee_id = get_post_meta( $post_id, '_sunset_assigned_employee', true );
	if ( ! $get_employee_id ) {
		return null;
	}

	$post = get_post( $get_employee_id );
	if ( ! $post ) {
		return null;
	}

	// Prepare the employee data.
	$employee_data = [
		'name'     => $post->post_title ?? '',
		'image_id' => get_post_thumbnail_id( $post->ID ) ?? '',
		'phone'    => get_post_meta( $post->ID, '_employee_phone_number', true ) ?? '',
		'email'    => get_post_meta( $post->ID, '_employee_email_address', true ) ?? '',
	];

	return $employee_data;
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
		$image_srcset = wp_get_attachment_image_srcset( $data['image_id'], 'thumbnail' );
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
