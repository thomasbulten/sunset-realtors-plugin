<?php

/**
 * Property listing meta (Block Editor).
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Listing;

/**
 * Listing meta.
 */
final class Listing_Meta {
	public const META_ASSIGNED_EMPLOYEE = '_sunset_assigned_employee';

	public const META_PRICE_CURRENCY = '_sunset_price_currency';

	public const META_MATTERPORT_ID = '_sunset_matterport_id';

	public const POST_TYPE = 'property';

	/**
	 * Initialize the listing meta.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', [ self::class, 'add_post_type_support' ], 11 );
		add_action( 'init', [ self::class, 'register_meta' ], 20 );
		add_action( 'enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ] );
		add_action( 'rest_after_insert_' . self::POST_TYPE, [ self::class, 'persist_rest_meta' ], 10, 3 );
	}

	/**
	 * @return void
	 */
	public static function add_post_type_support(): void {
		if ( post_type_exists( self::POST_TYPE ) ) {
			add_post_type_support( self::POST_TYPE, 'custom-fields' );
		}
	}

	/**
	 * @return void
	 */
	public static function register_meta(): void {
		if ( ! post_type_exists( self::POST_TYPE ) ) {
			return;
		}

		register_post_meta(
			self::POST_TYPE,
			self::META_ASSIGNED_EMPLOYEE,
			[
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => [
					'schema' => [
						'type'    => 'array',
						'items'   => [
							'type' => 'integer',
						],
						'default' => [],
					],
				],
				'default'           => [],
				'sanitize_callback' => [ self::class, 'sanitize_assigned_employees' ],
				'auth_callback'     => [ self::class, 'can_edit_meta' ],
			]
		);

		register_post_meta(
			self::POST_TYPE,
			self::META_PRICE_CURRENCY,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => [
					'schema' => [
						'type'    => 'string',
						'enum'    => [ 'EUR', 'USD', 'ANG' ],
						'default' => 'EUR',
					],
				],
				'default'           => 'EUR',
				'sanitize_callback' => [ self::class, 'sanitize_currency' ],
				'auth_callback'     => [ self::class, 'can_edit_meta' ],
			]
		);

		register_post_meta(
			self::POST_TYPE,
			self::META_MATTERPORT_ID,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => [
					'schema' => [
						'type'    => 'string',
						'default' => '',
					],
				],
				'default'           => '',
				'sanitize_callback' => [ self::class, 'sanitize_matterport_id' ],
				'auth_callback'     => [ self::class, 'can_edit_meta' ],
			]
		);
	}

	/**
	 * Ensure block editor meta updates are persisted for the property post type.
	 *
	 * @param \WP_Post          $post     Saved post.
	 * @param \WP_REST_Request  $request  REST request.
	 * @param bool              $creating Whether this is a new post.
	 * @return void
	 */
	public static function persist_rest_meta( \WP_Post $post, \WP_REST_Request $request, bool $creating ): void {
		unset( $creating );

		$meta = $request->get_param( 'meta' );

		if ( ! is_array( $meta ) ) {
			return;
		}

		if ( array_key_exists( self::META_ASSIGNED_EMPLOYEE, $meta ) ) {
			update_post_meta(
				$post->ID,
				self::META_ASSIGNED_EMPLOYEE,
				self::sanitize_assigned_employees( $meta[ self::META_ASSIGNED_EMPLOYEE ] )
			);
		}

		if ( array_key_exists( self::META_PRICE_CURRENCY, $meta ) ) {
			update_post_meta(
				$post->ID,
				self::META_PRICE_CURRENCY,
				self::sanitize_currency( $meta[ self::META_PRICE_CURRENCY ] )
			);
		}

		if ( array_key_exists( self::META_MATTERPORT_ID, $meta ) ) {
			update_post_meta(
				$post->ID,
				self::META_MATTERPORT_ID,
				self::sanitize_matterport_id( $meta[ self::META_MATTERPORT_ID ] )
			);
		}
	}

	/**
	 * @return void
	 */
	public static function enqueue_editor_assets(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'sunset-listing-meta',
			SUNSET_REALTORS_PLUGIN_DIR_URL . '/assets/js/block-editor/listing-meta.js',
			[
				'wp-plugins',
				'wp-edit-post',
				'wp-element',
				'wp-components',
				'wp-data',
				'wp-core-data',
				'wp-i18n',
			],
			SUNSET_REALTORS_PLUGIN_VERSION,
			true
		);

		$employees = [];

		foreach ( self::get_employee_options() as $employee_id => $employee_name ) {
			$employees[] = [
				'label' => $employee_name,
				'value' => (string) $employee_id,
			];
		}

		wp_add_inline_script(
			'sunset-listing-meta',
			'window.sunsetListingMeta = ' . wp_json_encode(
				[
					'domain'     => SUNSET_REALTORS_PLUGIN_DOMAIN,
					'postType'   => self::POST_TYPE,
					'meta'       => [
						'assignedEmployee' => self::META_ASSIGNED_EMPLOYEE,
						'priceCurrency'    => self::META_PRICE_CURRENCY,
						'matterportId'     => self::META_MATTERPORT_ID,
					],
					'employees'  => $employees,
					'currencies' => [ 'ANG', 'USD', 'EUR' ],
				]
			) . ';',
			'before'
		);
	}

	/**
	 * @param bool   $allowed Whether the user can add the meta.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id Post ID.
	 * @return bool
	 */
	public static function can_edit_meta( bool $allowed, string $meta_key, int $post_id ): bool {
		unset( $allowed, $meta_key );

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * @param mixed $value Meta value.
	 * @return array<int>
	 */
	public static function sanitize_assigned_employees( $value ): array {
		$ids = [];

		if ( is_array( $value ) ) {
			$ids = array_map( 'absint', $value );
		} elseif ( is_numeric( $value ) ) {
			$id = absint( $value );
			if ( $id > 0 ) {
				$ids = [ $id ];
			}
		}

		$ids = array_values( array_unique( array_filter( $ids ) ) );

		return array_values(
			array_filter(
				$ids,
				static function ( int $employee_id ): bool {
					$post = get_post( $employee_id );

					return $post instanceof \WP_Post && 'employee' === $post->post_type;
				}
			)
		);
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array<int>
	 */
	public static function get_assigned_employee_ids( int $post_id ): array {
		$value = get_post_meta( $post_id, self::META_ASSIGNED_EMPLOYEE, true );

		return self::sanitize_assigned_employees( $value );
	}

	/**
	 * @param mixed $value Meta value.
	 * @return string
	 */
	public static function sanitize_matterport_id( $value ): string {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * @param mixed $value Meta value.
	 * @return string
	 */
	public static function sanitize_currency( $value ): string {
		$currency = sanitize_text_field( (string) $value );

		return in_array( $currency, [ 'EUR', 'USD', 'ANG' ], true ) ? $currency : 'EUR';
	}

	/**
	 * @return array<int, string>
	 */
	public static function get_employee_options(): array {
		$employees = get_posts(
			[
				'post_type'      => 'employee',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			]
		);

		$options = [];

		foreach ( $employees as $employee ) {
			$options[ $employee->ID ] = $employee->post_title;
		}

		return $options;
	}
}
