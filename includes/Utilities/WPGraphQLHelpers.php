<?php
/**
 * Helper functions for WPGraphQL
 *
 * @package WPGraphQL\ContentBlocks\Utilities
 */

namespace WPGraphQL\ContentBlocks\Utilities;

use WPGraphQL\Utils\Utils;

/**
 * Class WPGraphQLHelpers
 */
final class WPGraphQLHelpers {
	/**
	 * Array of rendered blocks.
	 *
	 * @var array
	 */
	public static array $rendered_blocks = [];

	/**
	 * Formats the name of the block for the GraphQL registry
	 *
	 * @param string $name The name of the block.
	 */
	public static function format_type_name( string $name ): string {
		// No need to string-replace if there's no string.
		if ( empty( $name ) ) {
			return '';
		}

		// Format the type name for showing in the GraphQL Schema
		// @todo: WPGraphQL utility function should handle removing the '/' by default.
		$type_name = lcfirst( ucwords( $name, '/' ) );
		$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
		$type_name = null !== $type_name ? Utils::format_type_name( $type_name ) : '';

		return ! empty( $type_name ) ? Utils::format_type_name( $type_name ) : $type_name;
	}

	/**
	 * Gets the GraphQL type name for a provided block name.
	 *
	 * @internal This method will likely be removed in the future in favor of a block Model.
	 *
	 * @param ?string $block_name The name of the block.
	 */
	public static function get_type_name_for_block( ?string $block_name ): string {
		if ( empty( $block_name ) ) {
			$block_name = 'core/freeform';
		}

		$type_name = lcfirst( ucwords( $block_name, '/' ) );

		return self::format_type_name( $type_name );
	}

	/**
	 * Gets the rendered block.
	 *
	 * @param mixed|array{blockName: array, attrs: array, innerBlocks: string, innerHTML: string, innerContent: string, clientId: ?string} $block The block being resolved.
	 */
	public static function get_rendered_block( $block ): ?string {

		// As the parent method does not have block as an array and might be a breaking change
		// we are just ensuring this is an array
		if ( ! is_array( $block ) ) {
			// @phpstan-ignore-next-line
			return render_block( $block );
		}

		$key = $block['clientId'] ?? null;
		if ( ! is_string( $key ) ) {
			// Client ID is expected but bail if it doesn't exist
			return render_block( $block );
		}

		if ( array_key_exists( $key, self::$rendered_blocks ) ) {
			return self::$rendered_blocks[ $key ];
		}

		$content                       = render_block( $block );
		self::$rendered_blocks[ $key ] = $content;

		return $content;
	}
}
