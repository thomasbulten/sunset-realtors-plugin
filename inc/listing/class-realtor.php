<?php

/**
 * Manual realtor assignment per listing.
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Listing;

final class Realtor
{
    /**
     * @return void
     */
    public static function init(): void
    {
        add_filter('ysd_mapi_property_employee', [self::class, 'resolve_employee'], 10, 2);
    }

    /**
     * Resolve employee for a property listing.
     *
     * @param \WP_Post|null $employee  Pre-resolved employee.
     * @param int           $post_id   Property post ID.
     * @return \WP_Post|null
     */
    public static function resolve_employee(?\WP_Post $employee, int $post_id): ?\WP_Post
    {
        if ($employee instanceof \WP_Post) {
            return $employee;
        }

        $assigned_id = absint(get_post_meta($post_id, Listing_Meta::META_ASSIGNED_EMPLOYEE, true));

        if ($assigned_id > 0) {
            $assigned = get_post($assigned_id);

            if ($assigned instanceof \WP_Post && 'employee' === $assigned->post_type) {
                return $assigned;
            }
        }

        return self::resolve_by_accountmanager($post_id);
    }

    /**
     * @param int $post_id Property post ID.
     * @return \WP_Post|null
     */
    public static function resolve_by_accountmanager(int $post_id): ?\WP_Post
    {
        $accountmanager = get_post_meta($post_id, 'accountmanager', true);

        if ('' === (string) $accountmanager) {
            return self::get_standard_employee();
        }

        $query = new \WP_Query(
            [
                'post_type'      => 'employee',
                'posts_per_page' => 1,
                'meta_query'     => [
                    [
                        'key'     => 'accountmanager',
                        'value'   => $accountmanager,
                        'compare' => '=',
                    ],
                    [
                        'key'     => 'accountmanager',
                        'value'   => '',
                        'compare' => '!=',
                    ],
                ],
            ]
        );

        $employees = $query->get_posts();

        if (isset($employees[0])) {
            return $employees[0];
        }

        return self::get_standard_employee();
    }

    /**
     * @return \WP_Post|null
     */
    private static function get_standard_employee(): ?\WP_Post
    {
        $query = new \WP_Query(
            [
                'post_type'      => 'employee',
                'posts_per_page' => -1,
            ]
        );

        foreach ($query->get_posts() as $employee) {
            if ('standaardEmployee' === $employee->post_excerpt) {
                return $employee;
            }
        }

        return null;
    }

    /**
     * @param int $post_id Property post ID.
     * @return string
     */
    public static function get_employee_email(int $post_id): string
    {
        $employee = self::resolve_employee(null, $post_id);

        if (! $employee instanceof \WP_Post) {
            return '';
        }

        return (string) get_post_meta($employee->ID, '_employee_email_address', true);
    }
}
