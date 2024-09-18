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
}
