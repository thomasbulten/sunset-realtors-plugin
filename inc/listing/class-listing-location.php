<?php

/**
 * Location display: post_title instead of address, hide address fields.
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Listing;

final class Listing_Location
{
    /** @var array<string> */
    private const HIDDEN_ADDRESS_FIELDS = [
        'address_1',
        'house_number',
        'house_number_addition',
        'show_house_number',
        'zipcode',
    ];

    /**
     * @return void
     */
    public static function init(): void
    {
        add_filter('ysd_mapi_property_display_title', [self::class, 'filter_display_title'], 10, 2);
        add_filter('ysd_mapi_property_address', [self::class, 'filter_address'], 10, 3);
        add_filter('ysd_mapi_property_data_layout_items', [self::class, 'filter_data_layout_items'], 10, 3);
        add_filter('ysd_mapi_property_data_transfer_items', [self::class, 'filter_data_layout_items'], 10, 3);
        add_filter('ysd_mapi_property_map_all_houses', [self::class, 'filter_property_map_all_houses'], 10, 2);
    }

    /**
     * @param string   $title    Default title HTML.
     * @param \WP_Post $property Property post.
     * @return string
     */
    public static function filter_display_title(string $title, \WP_Post $property): string
    {
        return sprintf(
            '<div class="entry-title__location">%s</div>',
            esc_html($property->post_title)
        );
    }

    /**
     * @param string   $address  Address string.
     * @param \WP_Post $property Property post.
     * @param bool     $full     Full address flag.
     * @return string
     */
    public static function filter_address(string $address, \WP_Post $property, bool $full): string
    {
        unset($property, $full);
        return '';
    }

    /**
     * @param array<string, mixed> $data     Field data.
     * @param string               $name     Field name.
     * @param \WP_Post             $property Property post.
     * @return array<string, mixed>
     */
    public static function filter_data_layout_items(array $data, string $name, \WP_Post $property): array
    {
        unset($property);

        if (in_array($name, self::HIDDEN_ADDRESS_FIELDS, true)) {
            return [];
        }

        return $data;
    }

    /**
	 * Filter property map all houses.
	 *
	 * @param array<string, mixed> $all_houses All houses data.
	 * @param \WP_Post 			   $post       Post.
	 * @return array<string, mixed>
     */
    public static function filter_property_map_all_houses(array $all_houses, \WP_Post $post): array
    {
		// Replace address with post title, empty number and city.
		$all_houses['propertyAll']['address'] = $post->post_title;
		$all_houses['propertyAll']['number'] = '';
		$all_houses['propertyAll']['city'] = '';

        return $all_houses;
    }
}
