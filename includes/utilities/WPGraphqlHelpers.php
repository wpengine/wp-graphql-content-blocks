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
	 * @return string
	 */
	public static function format_type_name( $name ) {
		// Format the type name for showing in the GraphQL Schema
		// @todo: WPGraphQL utility function should handle removing the '/' by default.
		$type_name = lcfirst( ucwords( $name, '/' ) );
		$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
		$type_name = Utils::format_type_name( $type_name );
		return Utils::format_type_name( $type_name );
	}
}
